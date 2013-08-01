<?php
/** 
 * @category    Mana
 * @package     Mana_Seo
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
abstract class Mana_Seo_Helper_PageType extends Mana_Core_Helper_PageType {
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

    public function matchRoute($route) {
        return $this->_getCoreHelper()->matchRoute($route);
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl $token
     * @return bool
     */
    public function setPage($token) {
        $token->setRoute($this->getRoutePath());

        return true;
    }

    abstract public function getSuffixHistoryType();
    abstract public function getUrlKey($urlModel);
}