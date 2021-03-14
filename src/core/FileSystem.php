<?php

use RecursiveIteratorIterator as RIIterator;
use RecursiveDirectoryIterator as RDIterator;

abstract class FileSystem {

    /**
     * Returns the base name of a file/folder in a path.
     *
     * @param string $path
     *
     * @return string the base name
     */
    public static function getName(string $path): string {
        return basename($path);
    }

    /**
     * Checks if the passed $path parameter exists and is a file
     *
     * @param string $path
     *
     * @return boolean
     */
    public static function isFile(string $path): bool {
        return file_exists($path) && is_file($path);
    }

    /**
     * Checks if the passed $path parameter exists and is a folder
     *
     * @param string $path
     *
     * @return boolean
     */
    public static function isFolder(string $path): bool {
        return file_exists($path) && is_dir($path);
    }

    /**
     * Checks if the passed $path parameter exists and is readable
     *
     * @param string $path
     *
     * @return boolean
     */
    public static function isReadable(string $path): bool {
        return file_exists($path) && is_readable($path);
    }

    /**
     * Checks if the passed $path parameter exists and is writable
     *
     * @param string $path
     *
     * @return boolean
     */
    public static function isWriteable(string $path): bool {
        return file_exists($path) && is_writable($path);
    }

    /**
     * Checks if the passed $path parameter exists
     *
     * @param string $path
     *
     * @return boolean
     */
    public static function exists(string $path): bool {
        return file_exists($path);
    }

    /**
     * Creates a folder
     *
     * @param string $path        The path of the folder.
     * @param int    $mode        Permissions of the folder.
     * @param bool   $recursion   Whether to create all parent folders.
     *
     * @throws Exception  When target path is a file.
     * @throws Exception  When target path cannot be created.
     */
    public static function createFolder(string $path, int $mode = 0777, bool $recursion = true): void {
        if (self::isFolder($path)) {
            return;
        }

        if (self::isFile($path)) {
            throw new Exception(sprintf("Target '%s' already exists as a file", self::getName($path)));
        }

        $status = @mkdir($path, $mode, $recursion);
        if ( ! $status) {
            throw new Exception(sprintf("Folder '%s' could not be created", self::getName($path)));
        }
    }

    /**
     * Deletes a file/folder recursively
     *
     * @param string $path
     *
     * @throws Exception  When file/folder cannot be deleted
     */
    public static function delete(string $path): void {
        if (self::isFile($path)) {
            self::deleteFile($path);
        }
        elseif (self::isFolder($path)) {
            self::deleteFolder($path);
        }
    }

    /**
     * Deletes a file
     *
     * @param string $path
     *
     * @throws Exception  When passed parameter is a folder
     * @throws Exception  When deletion was unsuccessful
     */
    public static function deleteFile(string $path): void {
        if ( ! self::exists($path)) {
            return;
        }

        if (self::isFolder($path)) {
            throw new Exception(sprintf("Cannot delete file '%s' as it's a folder.", self::getName($path)));
        }

        @unlink($path);

        if (self::exists($path)) {
            throw new Exception(sprintf("File '%s' could not be deleted.", self::getName($path)));
        }
    }

    /**
     * Deletes an empty folder
     *
     * @param string $path
     *
     * @throws Exception  When passed parameter is a file
     * @throws Exception  When deletion was unsuccessful
     */
    public static function deleteEmptyFolder(string $path): void {
        if ( ! self::exists($path)) {
            return;
        }

        if (self::isFile($path)) {
            throw new Exception(sprintf("Cannot delete folder '%s' as it's a file.", self::getName($path)));
        }

        @rmdir($path);
        if (self::exists($path)) {
            throw new Exception(sprintf("Folder '%s' could not be deleted.", self::getName($path)));
        }
        
    }

    /**
     * Deletes folder recursively, including the target folder itself
     *
     * @param string $path
     *
     * @throws Exception  When passed parameter is a file
     * @throws Exception  When deletion was unsuccessful
     */
    public static function deleteFolder(string $path): void {
        self::deleteFolderContents($path);
        self::deleteEmptyFolder($path);
    }

    /**
     * Deletes folder recursively, but keeps target folder
     *
     * @param string $path
     *
     * @throws Exception  When passed parameter is a file
     * @throws Exception  When deletion was unsuccessful
     */
    public static function deleteFolderContents(string $path): void {
        if ( ! self::exists($path)) {
            return;
        }

        if (self::isFile($path)) {
            throw new Exception(sprintf("Cannot delete folder '%s' as it's a file.", self::getName($path)));
        }

        // delete the files using RecursiveIterator
        $files = new RIIterator(new RDIterator($path, RDIterator::SKIP_DOTS), RIIterator::CHILD_FIRST);
        foreach ($files as $fileinfo) {
            $item = $fileinfo->getPathname();
            if (self::isFile($item)) {
                self::deleteFile($item);
            }
            else {
                self::deleteEmptyFolder($item);
            }
        }
    }

    /**
     * Copy file/folder to destination recursively
     *
     * @param string $source
     * @param string $target
     *
     * @throws Exception  When source item does not exist
     * @throws Exception  When folder could not be created
     * @throws Exception  When file could not be copied
     */
    public static function copy(string $source, string $target) {
        if ( ! self::exists($source)) {
            throw new Exception(sprintf("Source file or folder '%s' does not exist", self::getName($source)));
        }

        if (self::isFile($source)) {
            self::copyFile($source, $target);
        }
        else {
            self::createFolder($target);
            $dir = dir($source);

            while (($entry = $dir->read()) !== false ) {
                if (in_array($entry, ['.', '..'])) {
                    continue;
                }

                $sourcePath = $source . '/' . $entry; 
                $targetPath = $target . '/' . $entry;

                if (self::isFolder($sourcePath)) {
                    self::copy($sourcePath, $targetPath);
                    continue;
                }
                self::copyFile($sourcePath, $targetPath);
            }

            $dir->close();
        }
    }

    /**
     * Copies a file to destination
     *
     * @param string $source
     * @param string $target
     *
     * @throws Exception  When source does not exist
     * @throws Exception  When copy was unsuccessful
     */
    public static function copyFile(string $source, string $target) {
        if ( ! self::isFile($source)) {
            throw new Exception(sprintf("Source file does not exist or is a folder.", self::getName($source)));
        }


        @copy($source, $target);

        if ( ! self::isFile($target)) {
            throw new Exception(sprintf("File '%s' could not be copied to destination folder", self::getName($source)));
        }
    }

    /**
     * Creates a file – either empty or with content
     *
     * @param string $file
     * @param string $content (optional)
     *
     * @throws Exception  When creation was unsuccessful
     */
    public static function createFile(string $file, string $content = NULL) {
        if ( ! isset($content)) {
            @touch($file);
        }
        else {
            @file_put_contents($file, $content);
        }

        if ( ! self::isFile($file)) {
            throw new Exception(sprintf("File '%s' could not be created.", self::getName($file)));
        }
    }

}