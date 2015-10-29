<?php
/** 
 * @category    Mana
 * @package     Mana_InfiniteScrolling
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_InfiniteScrolling_Block_Engine extends Mage_Core_Block_Text_List {
    protected $_modeHandlers = array();
    public function addModeHandler($mode, $handler) {
        $this->_modeHandlers[$mode] = $handler;

        return $this;
    }

    protected function _beforeToHtml() {
        $data = $this->getData();
        foreach (array('list_block_name', 'm_client_side_block', 'text') as $key) {
            if (isset($data[$key])) {
                unset($data[$key]);
            }
        }
        $this->setData('m_client_side_block', array_merge($data, array(
            // client side class
            'type' => 'Mana/InfiniteScrolling/Engine',

            // settings from layout XML
            'mode_handlers' => $this->jsonHelper()->encodeAttribute($this->_modeHandlers),

            // settings from System Configuration
            'url_key' => Mage::getStoreConfig('mana/ajax/url_key_infinitescrolling'),
            'route_separator' => Mage::getStoreConfig('mana/ajax/route_separator_filter'),
            'page_separator' => Mage::getStoreConfig('mana/ajax/page_separator'),
            'limit_separator' => Mage::getStoreConfig('mana/ajax/limit_separator'),
            'effect_duration' => Mage::getStoreConfig('mana_infinitescrolling/infinitescrolling/effect_duration'),
            'pages_per_show_more' => Mage::getStoreConfig('mana_infinitescrolling/infinitescrolling/pages_per_show_more'),
            'recover_scroll_progress_on_back' => Mage::getStoreConfig('mana_infinitescrolling/infinitescrolling/recover_scroll_progress_on_back'),

            // product count
            'product_count' => $this->_getProductCount(),
            'mode' => $this->_getMode(),
            'show_more_caption' => $this->infiniteScrollingHelper()->__("Show More..."),

        )));

        return $this;
    }

    protected function _getProductCount() {
        /* @var $layout Mage_Core_Model_Layout */
        $layout = Mage::getSingleton('core/layout');

        /* @var $engineBlock Mana_InfiniteScrolling_Block_Engine */
        /* @var $listBlock Mage_Catalog_Block_Product_List */
        if (($listBlockName = $this->getData('list_block_name')) &&
            ($listBlock = $layout->getBlock($listBlockName))
        ) {
            $collection = $listBlock->getLoadedProductCollection();
            return $collection->getSize();
        }
        else {
            return 0;
        }
    }

    protected function _getMode() {
        /* @var $layout Mage_Core_Model_Layout */
        $layout = Mage::getSingleton('core/layout');

        /* @var $engineBlock Mana_InfiniteScrolling_Block_Engine */
        /* @var $listBlock Mage_Catalog_Block_Product_List */
        if (($listBlockName = $this->getData('list_block_name')) &&
            ($listBlock = $layout->getBlock($listBlockName)) &&
            ($toolbarBlock = $listBlock->getToolbarBlock())
        ) {
            return $toolbarBlock->getCurrentMode();
        }
        else {
            return 'list';
        }
    }
    #region Dependencies

    /**
     * @return Mana_Core_Helper_UrlTemplate
     */
    public function urlTemplateHelper() {
        return Mage::helper('mana_core/urlTemplate');
    }

    /**
     * @return Mana_Core_Helper_Json
     */
    public function jsonHelper() {
        return Mage::helper('mana_core/json');
    }

    public function infiniteScrollingHelper() {
        return Mage::helper('mana_infinitescrolling');
    }

    #endregion
}