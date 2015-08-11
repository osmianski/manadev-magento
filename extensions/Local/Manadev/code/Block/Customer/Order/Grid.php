<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Block_Customer_Order_Grid extends Mage_Adminhtml_Block_Customer_Edit_Tab_Orders
{
    protected function _prepareColumns() {

        $this->addColumnAfter('status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'status',
            'type'  => 'options',
            'width' => '100px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ), 'grand_total');

        $this->addColumnAfter('download_status', array(
            'header' => Mage::helper('sales')->__('Download Status'),
            'index' => 'download_status',
            'filter_condition_callback' => function($collection, $column) {
                /* @var $resource Local_Manadev_Resource_Order */
                $resource = Mage::getResourceSingleton('local_manadev/order');
                $resource->addDownloadStatusCollectionFilter($collection, $column->getFilter()->getCondition());
            },
            'type'  => 'options',
            'width' => '150px',
			'options' => Mage::getSingleton('local_manadev/source_downloadStatus')->getOptionArray(),
        ), 'status');

        return parent::_prepareColumns();
    }

    protected function _prepareCollection() {
        /* @var $collection Mage_Sales_Model_Resource_Order_Grid_Collection */
        $collection = Mage::getResourceModel('sales/order_grid_collection')
            ->addFieldToSelect('entity_id')
            ->addFieldToSelect('increment_id')
            ->addFieldToSelect('customer_id')
            ->addFieldToSelect('created_at')
            ->addFieldToSelect('grand_total')
            ->addFieldToSelect('order_currency_code')
            ->addFieldToSelect('store_id')
            ->addFieldToSelect('billing_name')
            ->addFieldToSelect('shipping_name')
            ->addFieldToFilter('customer_id', Mage::registry('current_customer')->getId())
            ->setIsCustomerMode(true);

        $collection->addFieldToSelect('status');

        /* @var $resource Local_Manadev_Resource_Order */
        $resource = Mage::getResourceSingleton('local_manadev/order');
        $resource->addDownloadStatusToCollection($collection);
        $this->setCollection($collection);

        return $this->_basePrepareCollection();
    }

    protected function _basePrepareCollection() {
        if ($this->getCollection()) {

            $this->_preparePage();

            $columnId = $this->getParam($this->getVarNameSort(), $this->_defaultSort);
            $dir      = $this->getParam($this->getVarNameDir(), $this->_defaultDir);
            $filter   = $this->getParam($this->getVarNameFilter(), null);

            if (is_null($filter)) {
                $filter = $this->_defaultFilter;
            }

            if (is_string($filter)) {
                $data = $this->helper('adminhtml')->prepareFilterString($filter);
                $this->_setFilterValues($data);
            }
            else if ($filter && is_array($filter)) {
                $this->_setFilterValues($filter);
            }
            else if(0 !== sizeof($this->_defaultFilter)) {
                $this->_setFilterValues($this->_defaultFilter);
            }

            if (isset($this->_columns[$columnId]) && $this->_columns[$columnId]->getIndex()) {
                $dir = (strtolower($dir)=='desc') ? 'desc' : 'asc';
                $this->_columns[$columnId]->setDir($dir);
                $this->_setCollectionOrder($this->_columns[$columnId]);
            }

            if (!$this->_isExport) {
                $this->getCollection()->load();
                $this->_afterLoadCollection();
            }
        }

        return $this;
    }
}