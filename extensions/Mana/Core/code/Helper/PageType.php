<?php
/** 
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
abstract class Mana_Core_Helper_PageType extends Mage_Core_Helper_Abstract {
    protected $_code;

    abstract public function getRoutePath();
    abstract public function getCurrentSuffix();

    public function setCode($code) {
        $this->_code = $code;
        return $this;
    }
    public function getCode() {
        return $this->_code;
    }

    public function matchRoute($route) {
        return $route == $this->getRoutePath();
    }

    public function isProductListVisible() {
        return true;
    }

    /**
     * @return bool|string
     */
    public function getConditionLabel() {
        return false;
    }

    public function getPageContent() {
        return array(
            'page_type' => $this->getPageTypeId(),
            'meta_title' => Mage::getStoreConfig('design/head/default_title'),
            'meta_keywords' => Mage::getStoreConfig('design/head/default_keywords'),
            'meta_description' => Mage::getStoreConfig('design/head/default_description'),
            'meta_robots' => Mage::getStoreConfig('design/head/default_robots'),
            'title' => '',
            'description' => '',
        );
    }

    public function getPageTypeId() {
        return '';
    }
}