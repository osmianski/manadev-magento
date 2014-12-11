<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterContent
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
abstract class ManaPro_FilterContent_Helper_Action extends Mage_Core_Helper_Abstract {
    protected $_key;

    /**
     * @param Mage_Core_Model_Config_Element $xml
     */
    public function init($xml) {
        $this->_key = $xml->getName();
    }

    /**
     * @param array $content
     * @return array
     */
    public abstract function read($content);
}