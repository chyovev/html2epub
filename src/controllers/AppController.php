<?php

use Twig\TwigFunction;
use Twig\Loader\FilesystemLoader;

abstract class AppController {

    public $twig;

    ///////////////////////////////////////////////////////////////////////////
    public function __construct() {
        $this->_initiateTwigTemplateEngine();
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function _initiateTwigTemplateEngine(): void {
        if ( ! isset($this->twig)) {
            $loader = new FilesystemLoader(TEMPLATES_PATH);
            $twig   = new ExtendedTwig($loader);

            // registering the Url abstract class as custom_url function in twig
            $urlFunction = new TwigFunction('custom_url', function($method, ...$args) {
                return forward_static_call_array(['Url', $method], $args);    
            });
            $twig->addFunction($urlFunction);

            $this->twig = $twig;
        }
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function _setView(string $template, array $viewVars = []): void {
        $this->twig->view($template, $viewVars);
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function _setViewVars(array $viewVars) {
        $this->twig->addGlobals($viewVars);
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function _throw404OnEmpty($item): void {
        if ( ! $item) {
            $backtrace = debug_backtrace();
            throw new Exception('Backtrace: calling function "' . $backtrace[0]['function'] . '" in file ' . $backtrace[0]['file']);
        }
    }

}