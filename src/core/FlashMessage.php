<?php
// use the static methods of this class to set and get flash messages

abstract class FlashMessage {
    
    ///////////////////////////////////////////////////////////////////////////////
    public static function setFlashMessage(bool $status, string $message): void {
        $_SESSION['flash'] = [
            'status'  => $status,
            'message' => $message,
        ];
    }

    ///////////////////////////////////////////////////////////////////////////////
    public static function getFlashMessage(): array {
        $flash = $_SESSION['flash'] ?? [];
        self::unsetFlashMessage();

        return $flash;
    }

    ///////////////////////////////////////////////////////////////////////////////
    public static function unsetFlashMessage(): void {
        unset($_SESSION['flash']);
    }

}