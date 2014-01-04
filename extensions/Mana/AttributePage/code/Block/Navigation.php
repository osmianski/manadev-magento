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
class Mana_AttributePage_Block_Navigation extends Mage_Core_Block_Template {
    public function getHtml() {
        return $this->setTemplate('mana/attributepage/menu/attribute.phtml')->_toHtml();
    }

    public function getAttributePageCollection() {
        $collection = $this->createAttributePageCollection();
        switch (Mage::getStoreConfig('mana_attributepage/menu/sort_attribute_pages')) {
            case 'position-asc':
                $collection->setOrder('position', Varien_Data_Collection_Db::SORT_ORDER_ASC);
                break;
            case 'position-desc':
                $collection->setOrder('position', Varien_Data_Collection_Db::SORT_ORDER_DESC);
                break;
            case 'title-asc':
                $collection->setOrder('raw_title', Varien_Data_Collection_Db::SORT_ORDER_ASC);
                break;
            case 'title-desc':
                $collection->setOrder('raw_title', Varien_Data_Collection_Db::SORT_ORDER_DESC);
                break;
            default:
                $collection->setOrder('position', Varien_Data_Collection_Db::SORT_ORDER_ASC);
                break;
        }
        $collection
            ->getSelect()
                ->where('main_table.is_active = ?', 1)
                ->where('main_table.include_in_menu = ?', 1)
                ->where('main_table.store_id = ?', Mage::app()->getStore()->getId());

        $collection->load();
        return $collection;
    }

    public function getOptionPageCollection($attributePageGlobalId) {
        $collection = $this->createOptionPageCollection();
        switch (Mage::getStoreConfig('mana_attributepage/menu/sort_option_pages')) {
            case 'position-asc':
                $collection->setOrder('position', Varien_Data_Collection_Db::SORT_ORDER_ASC);
                break;
            case 'position-desc':
                $collection->setOrder('position', Varien_Data_Collection_Db::SORT_ORDER_DESC);
                break;
            case 'title-asc':
                $collection->setOrder('raw_title', Varien_Data_Collection_Db::SORT_ORDER_ASC);
                break;
            case 'title-desc':
                $collection->setOrder('raw_title', Varien_Data_Collection_Db::SORT_ORDER_DESC);
                break;
            default:
                $collection->setOrder('position', Varien_Data_Collection_Db::SORT_ORDER_ASC);
                break;
        }
        $collection
            ->addAttributePageFilter($attributePageGlobalId)
            ->getSelect()
                ->where('main_table.is_active = ?', 1)
                ->where('main_table.include_in_menu = ?', 1)
                ->where('main_table.store_id = ?', Mage::app()->getStore()->getId());

        if ($pageSize = Mage::getStoreConfig('mana_attributepage/menu/max_option_pages')) {
            $collection
                ->setPageSize($pageSize)
                ->setCurPage(1);
        }
        $collection->load();
        return $collection;
    }

    /**
     * @param Mana_AttributePage_Resource_OptionPage_Store_Collection $collection
     * @return bool
     */
    public function showLinkToAllOptionPages($collection) {
        switch (Mage::getStoreConfig('mana_attributepage/menu/show_all_option_pages')) {
            case 'always':
                return true;
            case 'if-max-reached':
                return $collection->getSize() > count($collection->getItems());
        }
        return false;
    }

    /**
     * @param Mana_AttributePage_Model_AttributePage_Store $page
     * @param Mana_AttributePage_Resource_OptionPage_Store_Collection $childCollection
     * @return bool
     */
    public function isAttributePageActive($page, $childCollection) {
        if ($this->coreHelper()->getRoutePath() == 'mana/attributePage/view' &&
            Mage::app()->getRequest()->getParam('id') == $page->getId())
        {
            return true;
        }

        foreach ($childCollection as $childPage) {
            if ($this->isOptionPageActive($childPage)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Mana_AttributePage_Model_OptionPage_Store $page
     * @return bool
     */
    public function isOptionPageActive($page) {
        return $this->coreHelper()->getRoutePath() == 'mana/optionPage/view' &&
            Mage::app()->getRequest()->getParam('id') == $page->getId();
    }

    #region Dependencies

    /**
     * @return Mana_AttributePage_Resource_AttributePage_Store_Collection
     */
    public function createAttributePageCollection() {
        return Mage::getResourceModel('mana_attributepage/attributePage_store_collection');
    }

    /**
     * @return Mana_AttributePage_Resource_OptionPage_Store_Collection
     */
    public function createOptionPageCollection() {
        return Mage::getResourceModel('mana_attributepage/optionPage_store_collection');
    }

    /**
     * @return Mana_Core_Helper_Data
     */
    public function coreHelper() {
        return Mage::helper('mana_core');
    }

    #endregion
}