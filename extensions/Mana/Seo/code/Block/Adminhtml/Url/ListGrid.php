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
class Mana_Seo_Block_Adminhtml_Url_ListGrid extends Mana_Admin_Block_V2_Grid  {
    public function __construct() {
        parent::__construct();
        $this->setDefaultSort('final_url_key');
        $this->setDefaultDir('asc');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    public function getSchemas() {
        return $this->createSchemaCollection()
            ->load()
            ->toOptionHash();
    }

    protected function _prepareColumns() {
        $this->addColumn('final_url_key', array(
            'header' => $this->__('URL Key'),
            'index' => 'final_url_key',
            'align' => 'left',
        ));
        $this->addColumn('status', array(
            'header' => $this->__('Status'),
            'index' => 'status',
            'width' => '50px',
            'align' => 'center',
            'type' => 'options',
            'options' =>$this->getStatusSourceModel()->getOptionArray(),
        ));
        $this->addColumn('position', array(
            'header' => $this->__('Position'),
            'index' => 'position',
            'align' => 'center',
            'width' => '50px',
        ));
        $this->addColumn('global_schema_id', array(
            'header' => $this->__('Schema'),
            'index' => 'global_schema_id',
            'width' => '100px',
            'align' => 'center',
            'type' => 'options',
            'options' => $this->getSchemas(),
        ));
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header' => $this->__('Store'),
                'index' => 'store_id',
                'type' => 'store',
                'store_all' => true,
                'store_view' => true,
                'sortable' => false,
            ));
        }
        $this->addColumn('internal_name', array(
            'header' => $this->__('Internal Name'),
            'index' => 'internal_name',
            'align' => 'left',
            'width' => '50px',
        ));
        $this->addColumn('final_include_filter_name', array(
            'header' => $this->__('Include Filter Name'),
            'index' => 'final_include_filter_name',
            'align' => 'center',
            'width' => '50px',
            'type' => 'options',
            'options' => $this->getYesNoSourceModel()->getOptionArray(),
        ));
        $this->addColumn('type', array(
            'header' => $this->__('Type'),
            'index' => 'type',
            'align' => 'center',
            'width' => '100px',
        ));
        $this->addColumn('is_page', array(
            'header' => $this->__('Is Page'),
            'index' => 'is_page',
            'align' => 'center',
            'width' => '50px',
            'type' => 'options',
            'options' => $this->getYesNoSourceModel()->getOptionArray(),
        ));
        $this->addColumn('is_parameter', array(
            'header' => $this->__('Is Parameter'),
            'index' => 'is_parameter',
            'align' => 'center',
            'width' => '50px',
            'type' => 'options',
            'options' => $this->getYesNoSourceModel()->getOptionArray(),
        ));
        $this->addColumn('is_attribute_value', array(
            'header' => $this->__('Is Attribute Value'),
            'index' => 'is_attribute_value',
            'align' => 'center',
            'width' => '50px',
            'type' => 'options',
            'options' => $this->getYesNoSourceModel()->getOptionArray(),
        ));
        $this->addColumn('is_category_value', array(
            'header' => $this->__('Is Category Value'),
            'index' => 'is_category_value',
            'align' => 'center',
            'width' => '50px',
            'type' => 'options',
            'options' => $this->getYesNoSourceModel()->getOptionArray(),
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareCollection() {
        $collection = $this->dbHelper()->getResourceModel("mana_seo/url_collection");

        /* @var $collection Mana_Seo_Resource_Url_Collection */
        $collection->addStoreAndGlobalSchemaColumns();

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
        return $this->adminHelper()->getStoreUrl('*/*/edit', array('id' => $row->getId()));
    }

    /**
     * @param Mana_Seo_Model_Url $row
     * @return string
     */
    public function getRowTitle($row) {
        return $this->__("Edit SEO URL '%s'", $row->getFinalUrlKey());
    }

    #region Dependencies
    /**
     * @return Mana_Seo_Model_Source_Schema_Status
     */
    public function getStatusSourceModel() {
        return Mage::getSingleton('mana_seo/source_url_status');
    }

    public function createSchemaCollection() {
        return $this->dbHelper()->getResourceModel('mana_seo/schema/flat_collection');
    }
    /**
     * @return Mana_Core_Model_Source_Yesno
     */
    public function getYesNoSourceModel() {
        return Mage::getSingleton('mana_core/source_yesno');
    }
    #endregion
}