<?php
require_once('../src/autoload.php');
require_once(CONTROLLER_PATH . '/AppController.php');

$directDispatcherRequest = preg_match('/\/'. preg_quote(basename(__FILE__)) . '/i', $_SERVER['REQUEST_URI']);

$controller = getGetRequestVar('controller') . 'Controller';
$action     = getGetRequestVar('action');


$phpFile    = CONTROLLER_PATH . '/' . $controller . '.php';

if ( ! $directDispatcherRequest && file_exists($phpFile)) {
    require_once($phpFile);
    $class = new $controller();
    
    if (method_exists($class, $action)) {
        try {
            $class->{$action}();
        }
        catch (Exception $e) {
            // TODO: log error
        }
    }
}

// if the request fails (no such controller, no such action)
// show 404 page
header('HTTP/1.1 404 Not Found'); 
$twig->view('layout/error404');