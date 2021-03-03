<?php

use Symfony\Component\Validator\ConstraintViolationList;

abstract class AppController {

    public $twig;

    ///////////////////////////////////////////////////////////////////////////
    public function __construct(ExtendedTwig $twig) {
        $this->twig = $twig;
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

    ///////////////////////////////////////////////////////////////////////////
    protected function reorganizeValidationErrors(?ConstraintViolationList $failures): array {
        $errors = [];

        foreach ($failures as $item) {
            $field            = $item->getPropertyPath();
            $message          = $item->getMessage();
            $errors[$field][] = $message;
        }

        return $errors;
    }

}