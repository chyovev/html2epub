<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\WebProcessor;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

abstract class Initializer {

    ///////////////////////////////////////////////////////////////////////////////
    public static function twig(): ExtendedTwig {
        $loader = new FilesystemLoader(TEMPLATES_PATH);
        $twig   = new ExtendedTwig($loader);

        // registering the Router static url function as custom_url in twig
        $urlFunction = new TwigFunction('custom_url', function($params) {
            global $twig;
            $globals = $twig->getGlobals();

            // use current controller and action if none provided
            $params[0]['controller'] = $params[0]['controller'] ?? $globals['_controller'] ?? NULL;
            $params[0]['action']     = $params[0]['action']     ?? $globals['_action']     ?? NULL;

            return forward_static_call_array(['Router', 'url'], $params);
        });
        $twig->addFunction($urlFunction);

        return $twig;
    }

    ///////////////////////////////////////////////////////////////////////////////
    public static  function monolog(): Logger {
        $logger = new Logger('HTML2ePub');
        $logger->pushHandler(new StreamHandler(LOG_FILE, Logger::DEBUG));

        // add additional information to errors
        $logger->pushProcessor(new IntrospectionProcessor());
        $logger->pushProcessor(new WebProcessor());

        return $logger;
    }
    
}