<?php
/** 
 * @category    Mana
 * @package     Mana_Seo
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Seo_Block_Adminhtml_Schema_ListGrid extends Mana_Admin_Block_V2_Grid  {
    public function __construct() {
        parent::__construct();
        $this->setDefaultSort('status');
        $this->setDefaultDir('asc');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareColumns() {
        $this->addColumn('status', array(
            'header' => $this->__('Status'),
            'index' => 'status',
            'width' => '50px',
            'align' => 'center',
            'type' => 'options',
            'options' =>$this->getStatusSourceModel()->getOptionArray(),
        ));
        $this->addColumn('name', array(
            'header' => $this->__('Name'),
            'index' => 'name',
            'width' => '200px',
            'align' => 'left',
        ));
        $this->addColumn('updated_at', array(
            'header' => $this->__('Last Updated'),
            'index' => 'updated_at',
            'type' => 'datetime',
            'width' => '150px',
        ));
        $this->addColumn('sample', array(
            'header' => $this->__('Sample URL'),
            'index' => 'sample',
            'align' => 'left',
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareCollection() {
        if ($this->adminHelper()->isGlobal()) {
            $collection = $this->dbHelper()->getResourceModel("mana_seo/schema/flat_collection");
        }
        else {
            $collection = $this->dbHelper()->getResourceModel("mana_seo/schema/store_flat_collection");
            $collection->addFieldToFilter('store_id', $this->adminHelper()->getStore()->getId());
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    public function getGridUrl() {
        return $this->adminHelper()->getStoreUrl('*/*/grid');
    }

    /**
     * @param Mana_Seo_Model_Schema $row
     * @return string
     */
    public function getRowUrl($row) {
        if ($this->adminHelper()->isGlobal()) {
            return $this->adminHelper()->getStoreUrl('*/*/edit', array('id' => $row->getPrimaryId()));
        }
        else {
            return $this->adminHelper()->getStoreUrl('*/*/edit', array('id' => $row->getPrimaryGlobalId(),
                'store' => $this->adminHelper()->getStore()->getId()));
        }
    }

    /**
     * @param Mana_Seo_Model_Schema $row
     * @return string
     */
    public function getRowTitle($row) {
            return $this->__("Edit SEO schema '%s'", $row->getName());
    }

    #region Dependencies
    /**
     * @return Mana_Seo_Model_Source_Schema_Status
     */
    public function getStatusSourceModel() {
        return Mage::getSingleton('mana_seo/source_schema_status');
    }
    #endregion
}