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
            throw new Exception('Trying to fetch non-existing database record or using wrong request method.');
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
            $this->setErrorFlash('Item could not be saved.');
        }
        else {
            try {
                $object->save();
                $this->setSuccessFlash('Item successfully saved!');
                return true;
            }
            catch (Exception $e) {
                $this->addCriticalError(get_class($object) . ' not saved: ' . $e->getMessage());
                $this->setErrorFlash('An error occurred. Please try again later.');
            }
        }

        return false;
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function getFlash(): array {
        return FlashMessage::getFlashMessage();
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function setErrorFlash(string $message): void {
        FlashMessage::setFlashMessage(false, $message);
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function setSuccessFlash(string $message): void {
        FlashMessage::setFlashMessage(true, $message);
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function generateFlashHtml(array $flash = []): string {
        if ( ! $flash) {
            $flash = $this->getFlash();
        }
        
        return $this->twig->render('elements/flash.message.twig', ['flash' => $flash, 'hidden' => true]);
    }

}