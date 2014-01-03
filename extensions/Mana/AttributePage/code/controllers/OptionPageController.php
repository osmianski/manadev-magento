<?php
/** 
 * @category    Mana
 * @package     Mana_AttributePage
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_AttributePage_OptionPageController extends Mage_Core_Controller_Front_Action {
    public function viewAction() {
        if ($optionPage = $this->_initOptionPage()) {
            $layoutXml = $this->_applyCustomDesign();
            Mage::getSingleton('mana_attributepage/layer')->apply();

            $this->getLayout()->getUpdate()->addHandle('default');
            $this->addActionLayoutHandles();
            $this->loadLayoutUpdates();
            if (trim($layoutXml)) {
                $this->getLayout()->getUpdate()->addUpdate($layoutXml);
            }
            $this->generateLayoutXml();
            $this->generateLayoutBlocks();
            $this->_isLayoutLoaded = true;
            if ($pageLayout = $optionPage->getData('page_layout')) {
                $this->pageLayoutHelper()->applyTemplate($pageLayout);
            }

            $this->_setProductListOptions();
            $this->renderLayout();
        }
        else {
            $this->_forward('noRoute');
        }
    }

    /**
     * @return bool|Mana_AttributePage_Model_OptionPage_Store
     */
    protected function _initOptionPage() {
        Mage::dispatchEvent('mana_attributepage_controller_option_page_init_before', array('controller_action' => $this));
        $optionPageId = (int) $this->getRequest()->getParam('id', false);
        if (!$optionPageId) {
            return false;
        }

        /* @var $optionPage Mana_AttributePage_Model_OptionPage_Store */
        $optionPage = Mage::getModel('mana_attributepage/optionPage_store');
        $optionPage->setData('store_id', Mage::app()->getStore()->getId());
        $optionPage->load($optionPageId);

        if (!$optionPage->canShow()) {
            return false;
        }
        Mage::register('current_option_page', $optionPage);
        try {
            Mage::dispatchEvent('mana_attributepage_controller_option_page_init_after', array('option_page' => $optionPage, 'controller_action' => $this));
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            return false;
        }

        /* @var $attributePage Mana_AttributePage_Model_AttributePage_Store */
        $attributePage = Mage::getModel('mana_attributepage/attributePage_store');
        $attributePage->setData('store_id', Mage::app()->getStore()->getId());
        $attributePage->load($optionPage->getData('attribute_page_global_id'), 'attribute_page_global_id');

        Mage::register('current_attribute_page', $attributePage );

        return $optionPage;
    }

    protected function _setProductListOptions() {
        /* @var $optionPage Mana_AttributePage_Model_OptionPage_Store */
        $optionPage = Mage::registry('current_option_page');
        if ($productList = $this->getLayout()->getBlock('product_list')) {
            /* @var $productList Mage_Catalog_Block_Product_List */
            $availableSortBy = array();
            $defaultSortBy = $this->getCatalogConfig()->getAttributeUsedForSortByArray();
            foreach (explode(',', $optionPage->getData('available_sort_by')) as $sortBy) {
                if (isset($defaultSortBy[$sortBy])) {
                    $availableSortBy[$sortBy] = $defaultSortBy[$sortBy];
                }
            }

            if ($availableSortBy) {
                $productList->setData('available_orders', $availableSortBy);
            }
            $productList->setData('sort_by', $optionPage->getData('default_sort_by'));
        }
    }

    protected function _applyCustomDesign() {
        /* @var $page Mana_AttributePage_Model_OptionPage_Store */
        $page = Mage::registry('current_option_page');

        $result = $page->getData('layout_xml');
        if (Mage::app()->getLocale()->isStoreDateInInterval(null,
            $page->getData('custom_design_active_from'),
            $page->getData('custom_design_active_to')))
        {
            $designInfo = explode("/", $page->getData('custom_design'));
            if (count($designInfo) == 2) {
                $this->getDesign()->setPackageName($designInfo[0])->setTheme($designInfo[1]);
            }
            $result .= $page->getData('custom_layout_xml');
        }
        return $result;
    }
    #region Dependencies

    /**
     * @return Mage_Catalog_Model_Config
     */
    public function getCatalogConfig() {
        return Mage::getSingleton('catalog/config');
    }

    /**
     * @return Mage_Core_Model_Design_Package
     */
    public function getDesign() {
        return Mage::getSingleton('core/design_package');
    }

    /**
     * @return Mage_Page_Helper_Layout
     */
    public function pageLayoutHelper() {
        return $this->getLayout()->helper('page/layout');
    }
    #endregion
}