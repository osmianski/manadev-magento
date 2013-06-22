<?php
/**
 * @category    Mana
 * @package     Mana_Ajax
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * Generic helper functions for Mana_Ajax module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class Mana_Ajax_Helper_Data extends Mage_Core_Helper_Abstract {
    protected $_detected = false;
    protected $_enabled = false;
    public function isEnabled() {
        if (!$this->_detected) {
            switch (Mage::getStoreConfig('mana/ajax/mode')) {
                case Mana_Ajax_Model_Mode::OFF:
                    break;
                case Mana_Ajax_Model_Mode::ON_FOR_ALL:
                    $this->_enabled = true;
                    break;
                case Mana_Ajax_Model_Mode::ON_FOR_USERS:
                    $this->_enabled = true;
                    foreach (explode(';', Mage::getStoreConfig('mana/ajax/bots')) as $agent) {
                        if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], trim($agent)) !== false) {
                            $this->_enabled = false;
                            break;
                        }
                    }
                    break;
                default:
                    throw new Exception('Not implemented');
            }
            $this->_detected = true;
        }
        return $this->_enabled;
    }
}