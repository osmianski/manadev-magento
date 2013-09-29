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
class Mana_Seo_Block_Adminhtml_Schema_SymbolGrid extends Mana_Admin_Block_V2_Grid {
    public function __construct() {
        parent::__construct();
        $this->setDefaultSort('symbol');
        $this->setDefaultDir('asc');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(false);
        $this->setStoreDataInBrowser(true);
    }

    protected function _prepareColumns() {
        $this->addColumn('edit_massaction', array(
            'header' => $this->__('Selected'),
            'index' => 'edit_massaction',
            'type' => 'massaction',
            'width' => '50px',
            'align' => 'center',
        ));
        $this->addColumn('symbol', array(
            'type' => 'input',
            'header' => $this->__('Symbol'),
            'index' => 'symbol',
            'width' => '100px',
            'align' => 'left',
        ));
        $this->addColumn('substitute', array(
            'type' => 'input',
            'header' => $this->__('Substitute'),
            'index' => 'substitute',
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareCollection() {
        $this->setCollection($this->getSchemaSymbolCollection()->setData(
            $this->getRaw() ? $this->getRaw() : $this->getFlatModel()->getJson('symbols')));
        return parent::_prepareCollection();
    }

    public function getGridUrl() {
        if ($this->adminHelper()->isGlobal()) {
            return $this->adminHelper()->getStoreUrl('*/*/symbolGrid', array(
                'id' => $this->getFlatModel()->getPrimaryId()
            ));
        }
        else {
            return $this->adminHelper()->getStoreUrl('*/*/symbolGrid', array(
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

    public function getMainButtonsHtml() {
        $html = '';
        $html .= $this->getChildHtml('add_button');
        $html .= $this->getChildHtml('remove_button');
        $html .= parent::getMainButtonsHtml();
        return $html;
    }

    protected function _prepareLayout() {
        /* @var $button Mana_Admin_Block_Grid_Action */
        $button = $this->getLayout()->createBlock('mana_admin/grid_action', "{$this->getNameInLayout()}.add")
            ->setData(array(
                'label' => $this->__('Add'),
                'class' => 'add'
            ));

        $this->setChild('add_button', $button);

        $button = $this->getLayout()->createBlock('mana_admin/grid_action', "{$this->getNameInLayout()}.remove")
            ->setData(array(
                'label' => $this->__('Remove Selected'),
                'class' => 'delete'
            ));

        $this->setChild('remove_button', $button);

        return parent::_prepareLayout();
    }

    public function getFieldName() {
        return 'symbols';
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
     * @return Mana_Seo_Resource_Schema_Symbol_Collection
     */
    public function getSchemaSymbolCollection () {
        return $this->dbHelper()->getResourceModel('mana_seo/schema_symbol_collection');
    }
    #endregion
}