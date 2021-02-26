<?php

abstract class AppController {

    protected $twig;

    ///////////////////////////////////////////////////////////////////////////
    public function __construct() {
        global $twig;

        $this->twig = $twig;
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function _throw404OnEmpty($item): void {
        if ( ! $item) {
            $backtrace = debug_backtrace();
            throw new Exception('Backtrace: calling function "' . $backtrace[0]['function'] . '" in file ' . $backtrace[0]['file']);
        }
    }

}