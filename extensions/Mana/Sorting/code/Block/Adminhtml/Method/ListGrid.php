<?php
/** 
 * @category    Mana
 * @package     Mana_Content
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Sorting_Block_Adminhtml_Method_ListGrid extends Mana_Admin_Block_V2_Grid {
    public function __construct() {
        parent::__construct();
        $this->setDefaultSort('title');
        $this->setDefaultDir('asc');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareColumns() {
        $this->addColumn(
            'title',
            array(
                'header' => $this->__('Title'),
                'index' => 'title',
                'width' => '200px',
                'align' => 'left',
            )
        );
        $this->addColumn(
            'position',
            array(
                'header' => $this->__('Position'),
                'index' => 'position',
                'width' => '50px',
                'align' => 'left',
            )
        );
        $this->addColumn(
            'is_active',
            array(
                'header' => $this->__('Status'),
                'index' => 'is_active',
                'width' => '50px',
                'align' => 'left',
                'type' => 'options',
                'options' => Mage::getSingleton('mana_core/source_status')->toOptionArray(),
            )
        );

        return parent::_prepareColumns();
    }

    protected function _prepareCollection() {
        if ($this->adminHelper()->isGlobal()) {
            $collection = Mage::getResourceModel("mana_sorting/method_collection");
        } else {
            $collection = Mage::getResourceModel("mana_sorting/method_store_collection");
            $collection->addFieldToFilter('store_id', $this->adminHelper()->getStore()->getId());
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    public function getGridUrl() {
        return $this->adminHelper()->getStoreUrl('*/*/grid');
    }

    public function getRowUrl($row) {
        if ($this->adminHelper()->isGlobal()) {
            return $this->adminHelper()->getStoreUrl(
                '*/*/edit',
                array(
                    'id' => $row->getId()
                )
            );
        } else {
            return $this->adminHelper()->getStoreUrl(
                '*/*/edit',
                array(
                    'id' => $row->getData('page_global_id'),
                    'store' => $this->adminHelper()->getStore()->getId()
                )
            );
        }
    }

    public function getRowTitle($row) {
        return $this->__("Edit Sorting Method '%s'", $row->getData('title'));
    }
}