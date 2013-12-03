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
class Mana_AttributePage_Block_Adminhtml_AttributePage_ListGrid extends Mana_Admin_Block_V2_Grid  {
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
            'width' => '200px',
            'align' => 'left',
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareCollection() {
        if ($this->adminHelper()->isGlobal()) {
            $collection = Mage::getResourceModel("mana_attributepage/attributePage_global_collection");
        }
        else {
            $collection = $this->dbHelper()->getResourceModel("mana_attributepage/attributePage_store_collection");
            $collection->addFieldToFilter('store_id', $this->adminHelper()->getStore()->getId());
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    public function getGridUrl() {
        return $this->adminHelper()->getStoreUrl('*/*/grid');
    }

    /**
     * @param Mana_AttributePage_Model_AttributePage_Abstract $row
     * @return string
     */
    public function getRowUrl($row) {
        if ($this->adminHelper()->isGlobal()) {
            return $this->adminHelper()->getStoreUrl('*/*/edit', array(
                'id' => $row->getId()
            ));
        }
        else {
            return $this->adminHelper()->getStoreUrl('*/*/edit', array(
                'id' => $row->getData('attribute_page_global_id'),
                'store' => $this->adminHelper()->getStore()->getId()
            ));
        }
    }

    /**
     * @param Mana_AttributePage_Model_AttributePage_Abstract $row
     * @return string
     */
    public function getRowTitle($row) {
            return $this->__("Edit Attribute Page '%s'", $row->getData('title'));
    }
}