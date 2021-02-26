<?php
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
function getRequestVariables(string $type, array $vars, $defaultNull = false) {
    $requestTypes = [
        'POST' => $_POST,
        'GET'  => $_GET,
    ];

    // only predetermined types are allowed 
    if ( ! array_key_exists(strtoupper($type), $requestTypes)) {
        throw new LogicException('Type ' . $type . ' is not supported');
    }

    $request = $requestTypes[strtoupper($type)];

    $result  = [];

    foreach ($vars as $var) {
        $result[$var] = isset($request[$var])
                      ? $request[$var]
                      : ($defaultNull ? NULL : '');
    }

    return $result;
}