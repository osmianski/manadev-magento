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
        $layout = Mage::getSingleton('core/layout');
        /* @var Mage_Page_Block_Html_Head $headBlock */
        $headBlock = $layout->getBlock('head');

        return array(
            'base_url' => Mage::getBaseUrl(),
            'page_type' => $this->getPageTypeId(),
            'meta_title' => Mage::getStoreConfig('design/head/default_title'),
            'meta_keywords' => Mage::getStoreConfig('design/head/default_keywords'),
            'meta_description' => Mage::getStoreConfig('design/head/default_description'),
            'meta_robots' => $headBlock ? $headBlock->getRobots() : Mage::getStoreConfig('design/head/default_robots'),
            'canonical_url' => $headBlock ? $this->getCanonicalUrl($headBlock) : null,
            'title' => '',
            'description' => '',
        );
    }

    public function getPageTypeId() {
        return '';
    }

    /**
     * @param Mage_Page_Block_Html_Head $headBlock
     * @return string|null
     */
    protected function getCanonicalUrl($headBlock) {
        foreach ($headBlock->getItems() as $item) {
            if (isset($item['type']) && $item['type'] == 'link_rel' &&
                isset($item['params']) && $item['params'] == 'rel="canonical"' )
            {
                return $item['name'];
            }
        }

        return null;
    }
}