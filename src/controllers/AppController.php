<?php

use Symfony\Component\Validator\ConstraintViolationList;
use Monolog\Logger;

abstract class AppController {

    public $twig;
    public $logger;

    ///////////////////////////////////////////////////////////////////////////
    public function __construct(ExtendedTwig $twig, Logger $logger) {
        $this->twig = $twig;
        $this->logger = $logger;
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
            throw new Exception('Page not found');
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

    ///////////////////////////////////////////////////////////////////////////
    public function addError(string $message): void {
        $this->logger->addError($message);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function addCriticalError(string $message): void {
        $this->logger->addCritical($message);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function saveWithValidation($object) {
        if ( ! $object->validate()) {
            FlashMessage::setFlashMessage(false, 'Item could not be saved.');
        }
        else {
            try {
                $object->save();
                FlashMessage::setFlashMessage(true, 'Item successfully saved!');
                return true;
            }
            catch (Exception $e) {
                $this->addCriticalError(get_class($object) . ' not saved: ' . $e->getMessage());
                FlashMessage::setFlashMessage(false, 'An error occurred. Please try again later.');
            }
        }

        return false;
    }

}