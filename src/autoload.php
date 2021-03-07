<?php
session_start();

// autoload.php gets included in every php file located in the /public folder.
// The file itself loads all other necessary files, config.php being the first of them.

require_once('core/config.php');

require_once(CORE_PATH   . '/functions.php');
require_once(CORE_PATH   . '/Router.php');
require_once(CORE_PATH   . '/FlashMessage.php');
require_once(VENDOR_PATH . '/autoload.php');
require_once(PROPEL_PATH . '/generated-conf/config.php');
require_once(TWIG_PATH   . '/ExtendedTwig.php');