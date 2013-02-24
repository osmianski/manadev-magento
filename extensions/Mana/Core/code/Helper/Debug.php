<?php
/**
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Core_Helper_Debug extends Mage_Core_Helper_Abstract
{
    public function logLayout($method) {
        $filename = "var/log/layout/" . uniqid() . ".xml";
        Mage::log("$method layout is dumped to {$filename}\n", Zend_Log::DEBUG, 'layout.log');
        $filename = BP.'/'.$filename;

        $out = '<?xml version="1.0" encoding="UTF-8"?>'."\n<!--\n";
        $out .= "Called from: \n    $method\n";
        $out .= "Applied handles: \n";

        /* @var $layout Mage_Core_Model_Layout */
        $layout = Mage::getSingleton('core/layout');

        foreach ($layout->getUpdate()->getHandles() as $handle) {
            $out .= "    $handle\n";
        }
        $out .= "-->\n";

        $out .= $layout->getNode()->asNiceXml('');
        if (!is_dir(dirname($filename))) {
            mkdir(dirname($filename), 0777, true);
        }
        file_put_contents($filename, $out);
    }
}