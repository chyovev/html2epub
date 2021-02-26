<?php

trait SaveTrait {

    public $errorMessage;

    ///////////////////////////////////////////////////////////////////////////
    public function saveWithValidation(): bool {
        if ( ! $this->validate()) {
            FlashMessage::setFlashMessage(false, 'Item could not be saved.');
        }
        else {
            try {
                $this->save();
                FlashMessage::setFlashMessage(true, 'Item successfully saved!');
                return true;
            }
            catch (Exception $e) {
                // TODO: log error
                FlashMessage::setFlashMessage(false, 'An error occurred. Please try again later.');
            }
        }

        return false;
    }
}