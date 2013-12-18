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
        $collection
            ->setOrder('title', 'ASC')
            ->getSelect()
                ->where('main_table.is_active = ?', 1)
                ->where('main_table.include_in_menu = ?', 1)
                ->where('main_table.store_id = ?', Mage::app()->getStore()->getId());

        $collection->load();
        return $collection;
    }

    public function getOptionPageCollection($attributePageGlobalId) {
        $collection = $this->createOptionPageCollection();
        $collection
            ->addAttributePageFilter($attributePageGlobalId)
            ->setOrder('title', 'ASC')
            ->getSelect()
                ->where('main_table.is_active = ?', 1)
                ->where('main_table.include_in_menu = ?', 1)
                ->where('main_table.store_id = ?', Mage::app()->getStore()->getId());

        $collection->load();
        return $collection;
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