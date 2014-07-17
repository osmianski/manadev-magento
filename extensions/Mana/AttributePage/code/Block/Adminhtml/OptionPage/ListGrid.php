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
class Mana_AttributePage_Block_Adminhtml_OptionPage_ListGrid extends Mana_Admin_Block_V2_Grid  {
    public function __construct() {
        parent::__construct();
        $this->setDefaultSort('title');
        $this->setDefaultDir('asc');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareColumns() {
        $this->addColumn('title', array(
            'header' => $this->__('Title'),
            'index' => 'title',
            'filter_index' => "`main_table`.`title`",
            'width' => '200px',
            'align' => 'left',
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareCollection() {
        if ($this->adminHelper()->isGlobal()) {
            $collection = Mage::getResourceModel("mana_attributepage/optionPage_global_collection");
            $collection->addFieldToFilter('attribute_page_global_id', $this->getAttributePage()->getId());
        }
        else {
            $collection = Mage::getResourceModel("mana_attributepage/optionPage_store_collection");
            $collection->addFieldToFilter('store_id', $this->adminHelper()->getStore()->getId());
            $collection->addAttributePageFilter($this->getAttributePage()->getId());
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    public function getGridUrl() {
        return $this->adminHelper()->getStoreUrl('*/*/grid', array(
            'parent_id' => $this->getAttributePage()->getId(),
        ));
    }

    /**
     * @param Mana_AttributePage_Model_AttributePage_Abstract $row
     * @return string
     */
    public function getRowUrl($row) {
        if ($this->adminHelper()->isGlobal()) {
            return $this->adminHelper()->getStoreUrl('*/*/edit', array(
                'parent_id' => $this->getAttributePage()->getId(),
                'id' => $row->getId()
            ));
        }
        else {
            return $this->adminHelper()->getStoreUrl('*/*/edit', array(
                'parent_id' => $this->getAttributePage()->getId(),
                'id' => $row->getData('attribute_page_global_id'),
                'store' => $this->adminHelper()->getStore()->getId()
            ));
        }
    }

    /**
     * @param Mana_AttributePage_Model_OptionPage_Abstract $row
     * @return string
     */
    public function getRowTitle($row) {
            return $this->__("Edit Option Page '%s'", $row->getData('title'));
    }

    #region Dependencies
    /**
     * @return Mana_AttributePage_Model_AttributePage_Abstract
     */
    public function getAttributePage() {
        if ($this->adminHelper()->isGlobal()) {
            return Mage::registry('m_attribute_page');
        }
        else {
            return Mage::registry('m_global_attribute_page');
        }
    }

    #endregion
}