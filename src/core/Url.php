<?php
// use the static methods of this class to generate URLs
// for all dynamic sections of the project

abstract class Url {
    
    ///////////////////////////////////////////////////////////////////////////////
    public static function generateBooksIndexUrl(): string {
        return ROOT . 'books';
    }

    ///////////////////////////////////////////////////////////////////////////////
    public static function generateBooksAddUrl(): string {
        return self::generateBooksIndexUrl() . '/add';
    }

    ///////////////////////////////////////////////////////////////////////////////
    public static function generateBookUrl(string $slug): string {
        return self::generateBooksIndexUrl() . '/' . $slug;
    }


}