<?php
define('VENDOR_PATH',    realpath(dirname(__FILE__) . '/../vendor'));
define('TWIG_PATH',      realpath(dirname(__FILE__) . '/twig'));
define('TEMPLATES_PATH', realpath(dirname(__FILE__) . '/../templates'));
define('ROOT',           preg_replace('/\/src/', '', substr(dirname(__FILE__), strlen($_SERVER['DOCUMENT_ROOT'])) . '/'));
