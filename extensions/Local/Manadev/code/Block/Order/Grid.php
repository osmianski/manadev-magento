<?php

class Local_Manadev_Block_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid {
    protected function _prepareMassaction() {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('order_ids');
        $this->getMassactionBlock()->setUseSelectAll(false);

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/cancel')) {
            $this->getMassactionBlock()->addItem('cancel_order', array(
                'label' => Mage::helper('sales')->__('Cancel'),
                'url' => $this->getUrl('*/sales_order/massCancel'),
            ));
        }

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/hold')) {
            $this->getMassactionBlock()->addItem('hold_order', array(
                'label' => Mage::helper('sales')->__('Hold'),
                'url' => $this->getUrl('*/sales_order/massHold'),
            ));
        }

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/unhold')) {
            $this->getMassactionBlock()->addItem('unhold_order', array(
                'label' => Mage::helper('sales')->__('Unhold'),
                'url' => $this->getUrl('*/sales_order/massUnhold'),
            ));
        }

        $this->getMassactionBlock()->addItem('pdfinvoices_order', array(
            'label' => Mage::helper('sales')->__('Print Invoices'),
            'url' => $this->getUrl('*/sales_order/pdfinvoices'),
        ));

        $this->getMassactionBlock()->addItem('pdfshipments_order', array(
            'label' => Mage::helper('sales')->__('Print Packingslips'),
            'url' => $this->getUrl('*/sales_order/pdfshipments'),
        ));

        $this->getMassactionBlock()->addItem('pdfcreditmemos_order', array(
            'label' => Mage::helper('sales')->__('Print Credit Memos'),
            'url' => $this->getUrl('*/sales_order/pdfcreditmemos'),
        ));

        $this->getMassactionBlock()->addItem('pdfdocs_order', array(
            'label' => Mage::helper('sales')->__('Print All'),
            'url' => $this->getUrl('*/sales_order/pdfdocs'),
        ));

        $this->getMassactionBlock()->addItem('print_shipping_label', array(
            'label' => Mage::helper('sales')->__('Print Shipping Labels'),
            'url' => $this->getUrl('*/sales_order_shipment/massPrintShippingLabel'),
        ));

        if (Mage::getStoreConfigFlag('local_manadev/accounting/recalculate')) {
            $this->getMassactionBlock()->addItem('recalculate', array(
                'label' => Mage::helper('sales')->__('Recalculate'),
                'url' => $this->getUrl('*/sales_order/recalculate'),
            ));
        }

        return $this;
    }

    protected function _prepareColumns() {
        $this->addColumnAfter('customer_email', array(
            'header' => Mage::helper('sales')->__('Customer Email'),
            'index' => 'customer_email',
            'filter_condition_callback' => function($collection, $column) {
                /* @var $resource Local_Manadev_Resource_Order */
                $resource = Mage::getResourceSingleton('local_manadev/order');
                $resource->addCustomerEmailCollectionFilter($collection, $column->getFilter()->getCondition());
            },
            'type'  => 'text',
            'width' => '300px',
        ), 'billing_name');

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


        parent::_prepareColumns();

        $this->removeColumn('shipping_name');
        $this->removeColumn('base_grand_total');
        if (!Mage::app()->isSingleStoreMode()) {
            $this->removeColumn('store_id');
        }

        return $this;
    }

    protected function _prepareCollection() {
        $collection = Mage::getResourceModel($this->_getCollectionClass());

        /* @var $resource Local_Manadev_Resource_Order */
        $resource = Mage::getResourceSingleton('local_manadev/order');
        $resource->addDownloadStatusToCollection($collection);
        $resource->addEmailsToCollection($collection);

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