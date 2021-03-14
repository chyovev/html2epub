<?php
require_once('../src/autoload.php');
require_once(CONTROLLER_PATH . '/AppController.php');

$twig = initiateTwig();
$logger = initiateMonologLogger();

$directDispatcherRequest = preg_match('/\/'. preg_quote(basename(__FILE__)) . '/i', $_SERVER['REQUEST_URI']);

if ($params = Router::getCurrentRequestParams()) {
    $controller      = $params['controller'];
    $action          = $params['action'];

    $controllerClass = ucfirst($controller) . 'Controller';
    $phpFile         = CONTROLLER_PATH . '/' . $controllerClass . '.php';
}

if ( ! $directDispatcherRequest && isset($phpFile) && file_exists($phpFile)) {
    require_once($phpFile);
    $class = new $controllerClass($twig, $logger);
    
    if (method_exists($class, $action)) {
        try {
            $twig->addGlobal('_controller', $controller);
            $twig->addGlobal('_action', $action);
            $class->{$action}();
        }
        catch (Exception $e) {
            $logger->addError($e->getMessage());
            $loggedError = true;
        }
    }
}

// if the request fails (no such controller, no such action)
// show 404 page
if ( ! isset($loggedError)) {
    $logger->addError('No such page');
}
header('HTTP/1.1 404 Not Found'); 
$twig->view('layout/error404');