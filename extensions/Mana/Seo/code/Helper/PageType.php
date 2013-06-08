<?php
/** 
 * @category    Mana
 * @package     Mana_Seo
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
abstract class Mana_Seo_Helper_PageType extends Mage_Core_Helper_Abstract {
    abstract public function getCurrentSuffix();
    abstract public function getSuffixHistoryType();
    abstract public function setPage($token);
}