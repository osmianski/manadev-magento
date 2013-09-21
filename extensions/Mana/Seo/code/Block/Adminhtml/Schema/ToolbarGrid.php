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
class Mana_Seo_Block_Adminhtml_Schema_ToolbarGrid extends Mana_Admin_Block_V2_Grid {
    public function __construct() {
        parent::__construct();
        $this->setDefaultSort('position');
        $this->setDefaultDir('asc');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(false);
        $this->setStoreDataInBrowser(true);
    }

    protected function _prepareColumns() {
        $this->addColumn('internal_name', array(
            'header' => $this->__('Code'),
            'index' => 'internal_name',
            'width' => '200px',
            'align' => 'left',
        ));
        $this->addColumn('name', array(
            'type' => 'input',
            'header' => $this->__('URL Key'),
            'index' => 'name',
        ));
        $this->addColumn('position', array(
            'type' => 'number_input',
            'header' => $this->__('Position'),
            'index' => 'position',
            'width' => '100px',
            'align' => 'center',
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareCollection() {
        $this->setCollection($this->getSchemaToolbarUrlKeyCollection()->setData(
            $this->getRaw() ? $this->getRaw() : $this->getFlatModel()->getJson('toolbar_url_keys')));

        return parent::_prepareCollection();
    }

    public function getGridUrl() {
        if ($this->adminHelper()->isGlobal()) {
            return $this->adminHelper()->getStoreUrl('*/*/toolbarGrid', array(
                'id' => $this->getFlatModel()->getPrimaryId()
            ));
        }
        else {
            return $this->adminHelper()->getStoreUrl('*/*/toolbarGrid', array(
                'id' => $this->getFlatModel()->getPrimaryGlobalId(),
                'store' => $this->adminHelper()->getStore()->getId()
            ));
        }
    }

    /**
     * @param Mana_Seo_Model_Schema $row
     * @return string
     */
    public function getRowUrl($row) {
        return false;
    }

    public function getFieldName() {
        return 'toolbar_url_keys';
    }

    #region Dependencies
    /**
     * @return Mana_Seo_Model_Schema
     */
    public function getFlatModel() {
        return Mage::registry('m_flat_model');
    }

    /**
     * @return Mana_Seo_Model_Schema
     */
    public function getEditModel() {
        return Mage::registry('m_edit_model');
    }

    /**
     * @return Mana_Seo_Resource_Schema_ToolbarUrlKey_Collection
     */
    public function getSchemaToolbarUrlKeyCollection() {
        return $this->dbHelper()->getResourceModel('mana_seo/schema_toolbarUrlKey_collection');
    }
    #endregion
}