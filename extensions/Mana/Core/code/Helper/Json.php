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
class Mana_Core_Helper_Json extends Mage_Core_Helper_Abstract {
    public function encodeAttribute($data, $options = array()) {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper('mana_core');

        $result = $core->jsonForceObjectAndEncode($data, $options);
        $result = implode("\"", str_replace("\"", "'", explode("'", $result)));
        $result = $this->escapeHtml($result);
        return $result;
    }
}