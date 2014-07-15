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
class Mana_InfiniteScrolling_Block_Options extends Mage_Core_Block_Abstract {
    /**
     * @return Mage_Catalog_Block_Product_List
     */
    public function getListBlock() {
        return $this->getLayout()->getBlock($this->getProductListBlockName());
    }
    public function getMode() {
        return $this->getListBlock()->getMode();
    }
    public function getPageCount() {
        return $this->getListBlock()->getLoadedProductCollection()->getLastPageNumber();
    }
    public function getAjaxUrl() {
        $params = array(
            '_current' => true,
            '_use_rewrite' => true,
            '_query' => array(
                'm-ajax' => 'scroll',
                'from_page' => '__0__',
                'page_count' => '__1__'
            ),
            '_secure' => Mage::app()->getFrontController()->getRequest()->isSecure()
        );
        return Mage::getUrl('*/*/*', $params);
    }
    protected function _toHtml() {
        Mage::helper('mana_core/js')->options('#m-infinite-scrolling-options', array(
            'content' => $this->getData($this->getMode().'_content'),
            'progressTemplate' => $this->getProgressTemplate(),
            'bufferSize' => Mage::getStoreConfig('mana_infinitescrolling/general/buffer_size'),
            'totalPages' => $this->getPageCount(),
            'url' => $this->getAjaxUrl(),
            'updateKey' => '.mb-' . str_replace('.', '-', $this->getProductListBlockName())
        ));
        return '';
    }
}