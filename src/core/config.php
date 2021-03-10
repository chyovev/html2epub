<?php
define('VENDOR_PATH',    realpath(dirname(__FILE__) . '/../../vendor'));
define('PROPEL_PATH',    realpath(dirname(__FILE__) . '/../models/propel'));
define('CORE_PATH',      realpath(dirname(__FILE__)));
define('CONTROLLER_PATH', realpath(dirname(__FILE__) . '/../controllers'));
define('TEMPLATES_PATH', realpath(dirname(__FILE__) . '/../views'));
define('LOG_FILE',       realpath(dirname(__FILE__) . '/../../') . '/logs/errors.log');
define('ROOT',           preg_replace('/\/src\/core/', '', substr(dirname(__FILE__), strlen($_SERVER['DOCUMENT_ROOT'])) . '/'));
define('ROOT_INTER',     realpath(dirname(__FILE__) . '/../..') . '/');
define('META_TITLE',     'HTML 2 ePub');
define('META_SUFFIX',    ' | ' . META_TITLE);