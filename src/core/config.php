<?php
define('VENDOR_PATH',    realpath(dirname(__FILE__) . '/../../vendor'));
define('PROPEL_PATH',    realpath(dirname(__FILE__) . '/../models/propel'));
define('TWIG_PATH',      realpath(dirname(__FILE__) . '/../core/twig'));
define('CORE_PATH',      realpath(dirname(__FILE__)));
define('CONTROLLER_PATH', realpath(dirname(__FILE__) . '/../controllers'));
define('TEMPLATES_PATH', realpath(dirname(__FILE__) . '/../views'));
define('ROOT',           preg_replace('/\/src\/core/', '', substr(dirname(__FILE__), strlen($_SERVER['DOCUMENT_ROOT'])) . '/'));
define('META_TITLE',     'HTML 2 ePub');
define('META_SUFFIX',    ' | ' . META_TITLE);