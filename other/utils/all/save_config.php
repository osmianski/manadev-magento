<?php

require_once 'app/Mage.php';
umask( 0 );
Mage::app('', 'store');
if (count($_GET)) {
    reset($_GET);
    $name = key($_GET);
    Mage::getConfig()->loadModulesConfiguration($name.'.xml')->getNode()
        ->asNiceXml(Mage::getBaseDir() . '/var/'. $name .'.xml');
}
else {
    Mage::getConfig()->getNode()->asNiceXml(Mage::getBaseDir() . '/var/config.xml');
}
