<?php

namespace Propel\Generator\Behavior\SingleImageUpload;

use Propel\Generator\Model\Behavior;
use Propel\Runtime\Connection\ConnectionInterface;

class SingleImageUploadBehavior extends Behavior {

    protected $parameters = [
        'table_column' => 'image',   // the name of the new column
        'group'   => 'image',   // $_FILES['group']
        'path'         => 'uploads', // where the files should be uploaded to
        'required'     => false,      // whether file is required
        'max_size_mb'  => 2,         // maximum size of file in MB
        'min_size_mb'  => 0,         // minimum size of file in MB
    ];

    ///////////////////////////////////////////////////////////////////////////
    // only one behavior instance per table is allowed for now
    public function allowMultiple() {
        return false;
    }

    ///////////////////////////////////////////////////////////////////////////
    // add the new column to the current table
    public function modifyTable() {
        $table = $this->getTable();

        $table->addColumn([
            'name' => $this->getParameter('table_column'),
            'type' => 'VARCHAR'
        ]);
    }

    ///////////////////////////////////////////////////////////////////////////
    // Get the setter of the behavior column, default being setImage()
    protected function getColumnSetter($column) {
        return 'set' . $this->getColumnForParameter($column)->getPhpName();
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function getColumnGetter($column) {
        return 'get' . $this->getColumnForParameter($column)->getPhpName();
    }

    ///////////////////////////////////////////////////////////////////////////
    public function postDelete($builder) {
        $script = '$this->deleteImage();';
        return $script;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function preSave($builder) {
        $script = '$this->uploadImage();';
        return $script;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function objectMethods($builder) {
        $group           = $this->getParameter('group');
        $path            = $this->getParameter('path');
        $getFunction     = $this->getColumnGetter('table_column');
        $setFunction     = $this->getColumnSetter('table_column');
        $isImageRequired = $this->booleanValue($this->getParameter('required'));

        $script = '
        ///////////////////////////////////////////////////////////////////////////
        private function uploadImage() {
            $uploadFile = $_FILES["' . $group . '"] ?? NULL;

            // if there’s no image to be uploaded, continue with the saving
            if ( ! $uploadFile) {
                return;
            }

            $doesOldFileExist = (bool) $this->' . $getFunction . 'Name();
            $noImageToUpload  = (bool) ($uploadFile[\'error\'] === UPLOAD_ERR_NO_FILE);';

            // required groups should throw an exception on empty file set
            if ($isImageRequired) {
                $script .= '
                // if image is required, and there is no currently uploaded file,
                // empty image set should provide an inambiguous message
                if ($doesOldFileExist && $noImageToUpload) {
                    return;
                }
                elseif ($noImageToUpload) {
                    $this->addValidationFailure("' . $group . '", "Please upload a file.");
                    throw new Exception("Upload file is required");
                }';
            }

            // non-required groups shouldn't be processed at all on empty file set
            else {
                $script .= '
                // if image is not required, empty image set should just be ignored
                if ($noImageToUpload) { 
                    return;
                }';
            }

            $script .= '
            $minSize = ' . $this->getParameter('min_size_mb') . ';
            $maxSize = ' . $this->getParameter('max_size_mb') . ';

            // get the old image src to delete it in case the new upload is successful
            $oldImageName = $this->' . $getFunction . 'Name();

            // try to save image; if successfully – delete old image
            try {
                $uploadPath = rtrim($this->getUploadPath(true), "/");
                $image      = new \ImageUpload($uploadFile, $uploadPath, [$minSize, $maxSize]);

                if ($image->upload()) {
                    $this->deleteImage($oldImageName);
                    $this->' . $setFunction . '($image->getJson());
                }
            }

            // if image upload fails, add error message as a validation failure
            // and abort the save
            catch (Exception $e) {
                $this->addValidationFailure("' . $group . '", $e->getMessage());
                throw $e;
            }
        }

        ///////////////////////////////////////////////////////////////////////////
        // get the name of the image
        public function ' . $getFunction . 'Name(): ?string {
            $json = $this->' . $getFunction . '();

            if ( ! $json) {
                return NULL;
            }

            $data = json_decode($json);

            return $data->name;
        }

        ///////////////////////////////////////////////////////////////////////////
        // get the image path
        public function ' . $getFunction . 'Src(): ?string {
            $name = $this->' . $getFunction . 'Name();

            if ( ! $name) {
                return NULL;
            }

            return $this->getUploadPath() . $name;
        }

        ///////////////////////////////////////////////////////////////////////////
        public function deleteImage(string $name = NULL): bool {
            // if no name is passed, get current name
            $filename = $name ?? $this->' . $getFunction . 'Name();

            if ( ! $filename) {
                return false;
            }

            $fullpath = $this->getUploadPath(true) . $filename;

            @unlink($fullpath);

            // if file still exists, return false
            return ( ! is_file($fullpath));
        }

        ///////////////////////////////////////////////////////////////////////////
        // internal URL is used for deleting old images,
        // otherwise public URL is generated for displaying images
        private function getUploadPath(bool $internal = false): string {
            $prefix = $internal ? (ROOT_INTER . "public/") : ROOT;

            return $prefix . "img/' . $path . '/";
        }

        ///////////////////////////////////////////////////////////////////////////
        private function addValidationFailure(string $field, string $message) {
            $failure = new \Symfony\Component\Validator\ConstraintViolation($message, NULL, [], NULL, "$field", NULL);
            $this->validationFailures[] = $failure;
        }';

        return $script;
    }

}
