<?php

use FileSystem as FS;

class ImageUpload  {

    const UPLOAD_ERRORS = [
        UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
        UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
        UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing tmp_dir directory.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.',
    ];

    // set in constructor
    protected $file;
    protected $location;
    protected $minFileSize = 0;
    protected $maxFileSize = 2097152; // 2 mbs in bits

    // set through public methods, optional
    protected $maxWidth  = 1920;
    protected $maxHeight = 1080;
    protected $allowedMimeTypes = ['jpeg', 'png'];

    // set internally during validation
    protected $fileName;
    protected $extension;
    protected $mimeType;
    protected $fileSize;
    protected $width;
    protected $height;

    ///////////////////////////////////////////////////////////////////////////
    // $file is passed as $_FILES[group] and is obligatory
    // $location and $maxSize can also be set later on, but before calling upload()
    public function __construct(array $file = [], string $location = NULL, array $maxSize = []) {
        if ( ! $file) {
            throw new LogicException('No upload array was provided');
        }

        if ($location) {
            $this->setLocation($location);
        }

        if ($maxSize) {
            $this->setFileSizeRange($maxSize);
        }

        $this->file = $file;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getFileName(): ?string {
        return $this->fileName;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getFileExtension(): ?string {
        return $this->extension;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getFileNameWithExtension(): ?string {
        return $this->fileName . '.' . $this->extension;
    }

    ///////////////////////////////////////////////////////////////////////////
    // called internally to set new file name
    protected function generateUniqueFileName(): self {
        $this->fileName = uniqid('', true).'_'.str_shuffle(implode(range('e', 'q')));

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getSize(): ?int {
        return $this->fileSize;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setFileSizeRange(array $size): self {
        list ($min, $max)  = $size;

        $this->setMinSize($min);
        $this->setMaxSize($max);

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getMaxSize(): float {
        // convert bits to MB
        return $this->maxFileSize / 1024 / 1024;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setMaxSize(float $mb): self {
        // convert MB to bits
        $this->maxFileSize = $mb * 1024 * 1024;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getMinSize(): float {
        // convert bits to MB
        return $this->minFileSize / 1024 / 1024;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setMinSize(float $mb): self {
        // convert MB to bits
        $this->minFileSize = $mb * 1024 * 1024;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getWidth(): ?int {
        return $this->width;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getHeight(): ?int {
        return $this->height;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setMaxDimensions(array $pxs): self {
        list ($width, $height)  = $pxs;

        $this->setMaxWidth($width);
        $this->setMaxHeight($height);

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getMaxWidth(): int {
        return $this->maxWidth;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setMaxWidth(int $px): self {
        $this->maxWidth = $px;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getMaxHeight(): int {
        return $this->maxHeight;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setMaxHeight(int $px): self {
        $this->maxHeight = $px;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getMimeType(): ?string {
        return $this->mimeType;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getAllowedMimeTypes(): array {
        return $this->allowedMimeTypes;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getLocation(): ?string {
        return $this->location;
    }

    ///////////////////////////////////////////////////////////////////////////
    // before setting location, check if can actually be used
    public function setLocation(string $path): self {
        $this->validateLocation($path);
        $this->location = $path;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setAllowedMimeTypes(array $types): self {
        $this->allowedMimeTypes = $types;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function validateLocation(string $path): void {
        $basename = basename($path);

        // if the folder does not exist, try to create it
        FS::createFolder($path);
        
        // make sure its writeable
        if ( ! FS::isWriteable($path)) {
            throw new Exception(sprintf("'%s' folder has no write permissions.", $basename));
        }
    }

    ///////////////////////////////////////////////////////////////////////////
    // when uploading an image, validate it first
    public function upload(): bool {
        $this->validateUploadImage();

        $status = @move_uploaded_file($this->file['tmp_name'], $this->getFullPath());

        if ( ! $status) {
            throw new Exception("File could not be moved to upload directory.");
        }

        return $status;
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function validateUploadImage(): void {
        // check if there was an internal upload error, and if so, abort
        $uploadError = self::UPLOAD_ERRORS[$this->file['error']] ?? NULL;
        
        if ($uploadError) {
            throw new Exception($uploadError);
        }

        // try to get the uploaded image properties
        $properties = @getimagesize($this->file['tmp_name']);

        // if that didn't work, the file is not an image - abort
        if ( ! $properties) {
            throw new Exception('Only images are allowed.');
        }

        // get constraints
        $allowedMimeTypes = $this->getAllowedMimeTypes();
        $maxWidth         = $this->getMaxWidth();
        $maxHeight        = $this->getMaxHeight();
        $minSizeMb        = $this->getMinSize();
        $maxSizeMb        = $this->getMaxSize();
        $maxSizeB         = $this->maxFileSize;
        $minSizeB         = $this->minFileSize;

        // set object properties
        $this->width      = $properties[0];
        $this->height     = $properties[1];
        $this->fileSize   = $properties['bits'];
        $this->mimeType   = $properties['mime'];
        $this->extension  = basename($this->mimeType); // use the suffix of the mimetype as an extension

        // keep generating new names in case of a filename collision
        do {
            $this->generateUniqueFileName();
        }
        while (FS::exists($this->getFullPath()));

        // validate against constraints:
        // check if extension (suffix of mime type) is allowed
        if ( ! in_array($this->extension, $allowedMimeTypes)) {
            var_dump($this->extension);die;
            throw new RangeException(sprintf("The file MIME type is not supported. Supported types are: %s", join(', ', $allowedMimeTypes)));
        }

        // make sure the image is not too wide
        if ($this->width > $maxWidth) {
            throw new RangeException(sprintf("Image exceeds maximum allowed width: %spx", $maxWidth));
        }

        // make sure the image is not too high
        if ($this->height > $maxHeight) {
            throw new RangeException(sprintf("Image exceeds maximum allowed height: %spx", $maxHeight));
        }

        // make sure the image is not too big size-wise
        if ($this->fileSize > $maxSizeB) {
            throw new RangeException(sprintf("Image exceeds maximum allowed file size: %s mb", $maxSizeMb));
        }

        // make sure the image is not too small size-wise
        if ($this->fileSize < $minSizeB) {
            throw new RangeException(sprintf("Image doesn't cover minimum allowed file size: %s mb", $minSizeMb));
        }
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getFullPath(): string {
        return $this->getLocation() . '/' . $this->getFileNameWithExtension();
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getJson() {
        return json_encode([
            'name'   => $this->getFileNameWithExtension(),
            'mime'   => $this->getMimeType(),
            'width'  => $this->getWidth(),
            'height' => $this->getHeight(),
            'size'   => $this->getSize(),
        ]);
    }
}