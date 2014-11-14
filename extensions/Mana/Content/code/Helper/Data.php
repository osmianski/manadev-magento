<?php
/** 
 * @category    Mana
 * @package     Mana_Content
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * Generic helper functions for Mana_Content module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class Mana_Content_Helper_Data extends Mage_Core_Helper_Abstract {
    public function underscoreToCapitalize($field) {
        return ucwords(str_replace("_", " ", $field));
    }

    public function underscoreToCamelcase($field) {
        return str_replace(" ", "", $this->underscoreToCapitalize($field));
    }
}