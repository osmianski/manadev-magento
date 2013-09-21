<?php
/**
 * @category    Mana
 * @package     Mana_AttributePage
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * Generic helper functions for Mana_AttributePage module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class Mana_AttributePage_Helper_Data extends Mage_Core_Helper_Abstract {
    public function getOptionPageSuffix() {
        return '.html';
    }

    public function getAttributePageSuffix() {
        return '.html';
    }
}