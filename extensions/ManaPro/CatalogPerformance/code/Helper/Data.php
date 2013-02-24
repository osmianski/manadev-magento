<?php
/**
 * @category    Mana
 * @package     ManaPro_CatalogPerformance
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * Generic helper functions for ManaPro_CatalogPerformance module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class ManaPro_CatalogPerformance_Helper_Data extends Mage_Core_Helper_Abstract {
    const CACHE_GROUP = 'block_html';

    public function beginCacheableGridItemMarkup($product) {
        return $this->_beginMarkup(new Varien_Object(array(
            'is_enabled' => Mage::getStoreConfigFlag('mana_performance/catalog_grid/is_enabled'),
            'cache_lifetime' => Mage::getStoreConfig('mana_performance/catalog_grid/lifetime') * 3600,
            'cache_key' => 'm_product_grid_block_' . $product->getStoreId() . '_' . $product->getId() . '_' .
                    (Mage::app()->getFrontController()->getRequest()->isSecure() ? 'secure' : '') . '_' .
                    Mage::app()->getStore($product->getStoreId())->getCurrentCurrencyCode(),
            'cache_tags' => array(self::CACHE_GROUP, 'catalog_product_' . $product->getId()),
        )));
    }
    public function endCacheableGridItemMarkup($handle) {
        $this->_endMarkup($handle);
    }

    public function beginCacheableListItemMarkup($product) {
        return $this->_beginMarkup(new Varien_Object(array(
            'is_enabled' => Mage::getStoreConfigFlag('mana_performance/catalog_list/is_enabled'),
            'cache_lifetime' => Mage::getStoreConfig('mana_performance/catalog_list/lifetime') * 3600,
            'cache_key' => 'm_product_list_block_' . $product->getStoreId() . '_' . $product->getId() . '_' .
                    (Mage::app()->getFrontController()->getRequest()->isSecure() ? 'secure' : '') . '_' .
                    Mage::app()->getStore($product->getStoreId())->getCurrentCurrencyCode(),
            'cache_tags' => array(self::CACHE_GROUP, 'catalog_product_' . $product->getId()),
        )));
    }
    public function endCacheableListItemMarkup($handle) {
        $this->_endMarkup($handle);
    }

    public function beginCacheableProductViewMarkup($product) {
        return $this->_beginMarkup(new Varien_Object(array(
            'is_enabled' => Mage::getStoreConfigFlag('mana_performance/catalog_product/is_enabled'),
            'cache_lifetime' => Mage::getStoreConfig('mana_performance/catalog_product/lifetime') * 3600,
            'cache_key' => 'm_product_view_block_' . $product->getStoreId() . '_' . $product->getId().'_'.$this->getCurrentPageKey() . '_' .
                    (Mage::app()->getFrontController()->getRequest()->isSecure() ? 'secure' : '').'_'.
                    Mage::getSingleton('customer/session')->getCustomerId() . '_' .
                    Mage::app()->getStore($product->getStoreId())->getCurrentCurrencyCode(),
            'cache_tags' => array(self::CACHE_GROUP, 'catalog_product_' . $product->getId()),
        )));
    }
    public function endCacheableProductViewMarkup($handle) {
        $this->_endMarkup($handle);
    }

    public function beginCacheableProductViewOptionsMarkup($product) {
        return $this->_beginMarkup(new Varien_Object(array(
            'is_enabled' => Mage::getStoreConfigFlag('mana_performance/catalog_product/is_enabled'),
            'cache_lifetime' => Mage::getStoreConfig('mana_performance/catalog_product/lifetime') * 3600,
            'cache_key' => 'm_product_view_block_options_' . $product->getStoreId() . '_' . $product->getId() . '_' . $this->getCurrentPageKey() . '_' .
                    (Mage::app()->getFrontController()->getRequest()->isSecure() ? 'secure' : '') . '_' .
                    Mage::app()->getStore($product->getStoreId())->getCurrentCurrencyCode(),
            'cache_tags' => array(self::CACHE_GROUP, 'catalog_product_' . $product->getId()),
        )));
    }
    public function endCacheableProductViewOptionsMarkup($handle) {
        $this->_endMarkup($handle);
    }

    protected function _beginMarkup($handle) {
        if (!$handle->getIsEnabled()) {
            return $handle;
        }
        elseif ($html = $this->_loadHtmlCache($handle)) {
            echo $html;
            return false;
        }
        else {
            ob_start();
            return $handle;
        }
    }
    protected function _endMarkup($handle) {
        if ($handle->getIsEnabled()) {
            $html = ob_get_clean();
            echo $html;
            $this->_saveHtmlCache($handle, $html);
        }
    }

    protected function _loadHtmlCache($handle) {
        if (is_null($handle->getCacheLifetime()) || !Mage::app()->useCache(self::CACHE_GROUP)) {
            return false;
        }
        $cacheKey = $handle->getCacheKey();
        /** @var $session Mage_Core_Model_Session */
        $session = Mage::getSingleton('core/session');
        $cacheData = Mage::app()->loadCache($cacheKey);
        if ($cacheData) {
            $cacheData = str_replace(
                $this->_getSidPlaceholder($cacheKey),
                    $session->getSessionIdQueryParam() . '=' . $session->getEncryptedSessionId(),
                $cacheData
            );
        }
        return $cacheData;
    }
    protected function _saveHtmlCache($handle, $data) {
        if (is_null($handle->getCacheLifetime()) || !Mage::app()->useCache(self::CACHE_GROUP)) {
            return false;
        }
        $cacheKey = $handle->getCacheKey();
        /** @var $session Mage_Core_Model_Session */
        $session = Mage::getSingleton('core/session');
        $data = str_replace(
            $session->getSessionIdQueryParam() . '=' . $session->getEncryptedSessionId(),
            $this->_getSidPlaceholder($cacheKey),
            $data
        );

        Mage::app()->saveCache($data, $cacheKey, $handle->getCacheTags(), $handle->getCacheLifetime());
        return $this;
    }

    protected function _getSidPlaceholder($cacheKey = null) {
        return '<!--SID=' . $cacheKey . '-->';
    }
    public function getCurrentPageKey() {
        /* @var $request Mage_Core_Controller_Request_Http */$request = Mage::app()->getRequest();
        return $request->getModuleName().'_'.$request->getControllerName() . '_' .$request->getActionName();
    }
}