<?php

abstract class Request {

    ///////////////////////////////////////////////////////////////////////////////
    public static function isType(string $type): bool {
        return (strtolower($_SERVER['REQUEST_METHOD']) === strtolower($type));
    }

    ///////////////////////////////////////////////////////////////////////////////
    public static function isPost(): bool {
        return self::isType('post');
    }

    ///////////////////////////////////////////////////////////////////////////////
    public static function isGet(): bool {
        return self::isType('get');
    }

    ///////////////////////////////////////////////////////////////////////////////
    public static function isAjax(): bool {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
               && (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'));
    }

    ///////////////////////////////////////////////////////////////////////////////
    public static function getPostVar(string $var) {
        $vars = self::getVars('POST', [$var], true);

        return $vars[$var];
    }

    ///////////////////////////////////////////////////////////////////////////////
    public static function getVars(string $type, array $vars = [], $defaultNull = false): array {
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
    
}