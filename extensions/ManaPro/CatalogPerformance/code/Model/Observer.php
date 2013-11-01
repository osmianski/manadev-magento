<?php
/**
 * @category    Mana
 * @package     ManaPro_CatalogPerformance
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * This class observes certain (defined in etc/config.xml) events in the whole system and provides public methods -
 * handlers for these events.
 * @author Mana Team
 *
 */
class ManaPro_CatalogPerformance_Model_Observer extends Mage_Core_Helper_Abstract {
    const CACHE_GROUP = 'block_html';

    /**
     * Makes product view cacheable (handles event "core_block_abstract_to_html_before")
     * @param Varien_Event_Observer $observer
     */
    public function setCacheSettings($observer) {
        /* @var $block Mage_Core_Block Abstract */ $block = $observer->getEvent()->getBlock();

//        if (Mage::getStoreConfigFlag('mana_performance/catalog_product/is_enabled') &&
//            $block->getTemplate() == Mage::getStoreConfig('mana_performance/catalog_product/template') &&
//            !$block->getIsCachingDisabled())
//        {
//            $block->setCacheLifetime(Mage::getStoreConfig('mana_performance/catalog_product/lifetime') * 3600);
//            $block->setCacheKey('m_product_view_block_' . $block->getProduct()->getStoreId().'_'. $block->getProduct()->getId());
//            $block->setCacheTags(array(self::CACHE_GROUP, 'catalog_product_' . $block->getProduct()->getId()));
//        }
        if (Mage::getStoreConfigFlag('mana_performance/catalog_top_navigation/is_enabled') &&
            $block->getTemplate() == Mage::getStoreConfig('mana_performance/catalog_top_navigation/template') &&
            !$block->getIsCachingDisabled())
        {
            $block->setCacheLifetime(Mage::getStoreConfig('mana_performance/catalog_top_navigation/lifetime') * 3600);
            $block->setCacheKey('m_top_navigation_block_' . Mage::app()->getStore()->getId() . '_' .
                    (Mage::app()->getFrontController()->getRequest()->isSecure() ? 'secure' : ''));
            $block->setCacheTags(array(self::CACHE_GROUP, 'm_catalog_category'));
        }
        if (Mage::getStoreConfigFlag('mana_performance/catalog_layered_navigation/is_enabled') &&
            $block->getTemplate() == Mage::getStoreConfig('mana_performance/catalog_layered_navigation/template') &&
            !$block->getIsCachingDisabled() && $block->getLayer() && $block->getChild('layer_state') &&
            !count($block->getChild('layer_state')->getActiveFilters()) &&
            ! ($block instanceof Mage_CatalogSearch_Block_Layer)
        ) {
            $block->setCacheLifetime(Mage::getStoreConfig('mana_performance/catalog_layered_navigation/lifetime') * 3600);
            $block->setCacheKey('m_layered_navigation_block_' .
                Mage::app()->getStore()->getId().'_'.
                $block->getLayer()->getCurrentCategory()->getId().'_'.
                (Mage::app()->getFrontController()->getRequest()->isSecure() ? 'secure' : '') . '_' .
                        Mage::app()->getStore()->getCurrentCurrencyCode()
            );
            $block->setCacheTags(array(self::CACHE_GROUP, 'm_catalog_product', 'm_catalog_category'));
        }
        if (Mage::getStoreConfigFlag('mana_performance/checkout_cart_sidebar/is_enabled') &&
                $block->getTemplate() == Mage::getStoreConfig('mana_performance/checkout_cart_sidebar/template') &&
                !$block->getIsCachingDisabled()
        ) {
            $block->setCacheLifetime(Mage::getStoreConfig('mana_performance/checkout_cart_sidebar/lifetime') * 3600);
            $block->setCacheKey('m_checkout_cart_sidebar_' .
                        Mage::app()->getStore()->getId() . '_' .
                        ($block->getQuote() ? $block->getQuote()->getId() : 'null') . '_' .
                        $block->getParentBlock()->getNameInLayout() . '_' .
                        (Mage::app()->getFrontController()->getRequest()->isSecure() ? 'secure' : '') . '_' .
                        Mage::app()->getStore()->getCurrentCurrencyCode()
            );
            $block->setCacheTags(array(self::CACHE_GROUP, 'm_quote_'. ($block->getQuote() ? $block->getQuote()->getId() : 'null')));
        }
    }
    /**
     * @deprecated
     * @param $observer
     */
    public function setProductViewCacheSettings($observer) {
        $this->setCacheSettings($observer);
    }

    /**
     * Makes product related caches obsolete (handles event "catalog_product_save_commit_after")
     * @param Varien_Event_Observer $observer
     */
    public function refreshProductCache($observer) {
        Mage::app()->cleanCache('m_catalog_product');
    }
    /**
     * Makes category related caches obsolete (handles event "catalog_category_save_commit_after")
     * @param Varien_Event_Observer $observer
     */
    public function refreshCategoryCache($observer) {
        Mage::app()->cleanCache('m_catalog_category');
    }
    /**
     * Makes quote caches obsolete (handles event "sales_quote_save_commit_after")
     * @param Varien_Event_Observer $observer
     */
    public function refreshQuoteCache($observer) {
        if (Mage::getStoreConfigFlag('mana_performance/checkout_cart_sidebar/is_enabled')) {
            Mage::app()->cleanCache('m_quote_' . $observer->getDataObject()->getId());
        }
    }
}