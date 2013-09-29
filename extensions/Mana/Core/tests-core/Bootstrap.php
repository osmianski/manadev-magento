<?php

define('_TEST', true);

require_once '../app/Mage.php';
Mage::app("default")
    ->setUseSessionInUrl(false);
Mage::setIsDeveloperMode(true);
Mage_Core_Model_Resource_Setup::applyAllUpdates();

if (!function_exists('test_autoload')) {
    function test_autoload($class)
    {
        $file = dirname(__FILE__) . DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
    spl_autoload_register('test_autoload');
}

Mana_Core_Test_Case::installTestData();
