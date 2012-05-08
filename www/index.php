<?php

define("APPLICATION_REV", "1.4");
define("APPLICATION_VERSION", "0.1");

defined("APPLICATION_PATH")
    || define("APPLICATION_PATH", realpath(dirname(__FILE__) . "/../app"));

defined("APPLICATION_ENV")
    || define("APPLICATION_ENV", (getenv("APPLICATION_ENV") ? getenv("APPLICATION_ENV") : "production"));


set_include_path(
    realpath(dirname(__FILE__) . "/../lib").PATH_SEPARATOR.
    get_include_path()
);

require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();

$application = new Zend_Application(APPLICATION_ENV);

$config = new Zend_Config(array(), true);

$confDir = APPLICATION_PATH."/configs";
$config->merge(new Zend_Config_Ini($confDir."/sys.ini"));

try {
    $config->merge(new Zend_Config_Ini($confDir."/config.ini"));
} catch (Zend_Config_Exception $e) {
    require APPLICATION_PATH."/../install/index.php";
    exit;
}
$application->setOptions($config->toArray())
    ->bootstrap()->run();