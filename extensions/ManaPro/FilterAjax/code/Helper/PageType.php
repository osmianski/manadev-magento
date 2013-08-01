<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterAjax
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
abstract class ManaPro_FilterAjax_Helper_PageType extends Mana_Core_Helper_PageType {
    protected function _getCoreHelper() {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper('mana_core');

        return $core->getPageType($this->getCode());
    }

    public function getRoutePath() {
        return $this->_getCoreHelper()->getRoutePath();
    }

    public function getCurrentSuffix() {
        return $this->_getCoreHelper()->getCurrentSuffix();
    }
}