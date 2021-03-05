<?php
///////////////////////////////////////////////////////////////////////////////
function isRequestAjax(): bool {
    return (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
           && (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'));
}

///////////////////////////////////////////////////////////////////////////////
function isRequest(string $type): bool {
    return (strtolower($_SERVER['REQUEST_METHOD']) === strtolower($type));
}

///////////////////////////////////////////////////////////////////////////////
function getGetRequestVar(string $var) {
    $vars = getRequestVariables('GET', [$var], true);
    return $vars[$var];
}

///////////////////////////////////////////////////////////////////////////////
function getPostRequestVar(string $var) {
    $vars = getRequestVariables('POST', [$var], true);
    return $vars[$var];
}

///////////////////////////////////////////////////////////////////////////////
function getRequestVariables(string $type, array $vars = [], $defaultNull = false) {
    $requestTypes = [
        'POST' => $_POST,
        'GET'  => $_GET,
    ];

    // only predetermined types are allowed 
    if ( ! array_key_exists(strtoupper($type), $requestTypes)) {
        throw new LogicException('Type ' . $type . ' is not supported');
    }

    $request = $requestTypes[strtoupper($type)];
    $default = $defaultNull ? NULL : '';

    $result  = array_fill_keys($vars, $default);

    // if $vars is specified, add only respective fields
    // otherwise add all fields in request
    foreach ($request as $var => $value) {
        if ($vars && ! in_array($var, $vars)) {
            continue;
        }

        $result[$var] = $request[$var]
                      ? $request[$var]
                      : $default;
    }

    return $result;
}

///////////////////////////////////////////////////////////////////////////////
function initiateTwig(): ExtendedTwig {
    $loader = new Twig\Loader\FilesystemLoader(TEMPLATES_PATH);
    $twig   = new ExtendedTwig($loader);

    // registering the Url abstract class as custom_url function in twig
    $urlFunction = new Twig\TwigFunction('custom_url', function($method, ...$args) {
        return forward_static_call_array(['Url', $method], $args);    
    });
    $twig->addFunction($urlFunction);

    return $twig;
}

///////////////////////////////////////////////////////////////////////////////
function initiateMonologLogger(): Monolog\Logger {
    $logger = new Monolog\Logger('HTML2ePub');
    $logger->pushHandler(new Monolog\Handler\StreamHandler(LOG_FILE, Monolog\Logger::DEBUG));

    // add additional information to errors
    $logger->pushProcessor(new Monolog\Processor\WebProcessor());
    $logger->pushProcessor(new Monolog\Processor\IntrospectionProcessor());

    return $logger;
}