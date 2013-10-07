<?php

class Local_Manadev_Block_Invoice_Grid extends Mage_Adminhtml_Block_Sales_Invoice_Grid {
    protected function _prepareColumns()
    {
        $this->addColumn('m_sales_account', array(
            'header' => Mage::helper('sales')->__('Bookkeeping Account'),
            'index' => 'm_sales_account',
        ));

        $this->addColumn('m_is_business', array(
            'header' => Mage::helper('sales')->__('Business Name'),
            'index' => 'm_is_business',
        ));

        $this->addColumn('increment_id', array(
            'header'    => Mage::helper('sales')->__('Invoice #'),
            'index'     => 'increment_id',
            'type'      => 'text',
        ));

        $this->addColumn('created_at', array(
            'header'    => Mage::helper('sales')->__('Invoice Date'),
            'index'     => 'created_at',
            'type'      => 'datetime',
        	'format'	=> 'y.MM.dd',
        ));

        $this->addColumn('order_increment_id', array(
            'header'    => Mage::helper('sales')->__('Order #'),
            'index'     => 'order_increment_id',
            'type'      => 'text',
        ));

        $this->addColumn('billing_name', array(
            'header' => Mage::helper('sales')->__('Bill to Name'),
            'index' => 'billing_name',
        ));

        $this->addColumn('grand_total', array(
            'header'    => Mage::helper('customer')->__('Amount'),
            'index'     => 'grand_total',
            'type'      => 'number',
            'align'     => 'right',
        ));

        $this->addColumn('currency', array(
            'header'    => Mage::helper('customer')->__('Currency'),
            'index'     => 'order_currency_code',
            'type'      => 'text',
            'align'     => 'center',
        ));
        $this->addColumn('m_exchange_rate', array(
            'header'    => Mage::helper('customer')->__('Exchange Rate'),
            'index'     => 'm_exchange_rate',
            'type'      => 'number',
            'align'     => 'center',
        ));
        $this->addColumn('m_grand_total', array(
            'header'    => Mage::helper('customer')->__('Suma LTL su PVM'),
            'index'     => 'm_grand_total',
            'type'      => 'number',
            'align'     => 'right',
        ));
        $this->addColumn('m_total', array(
            'header'    => Mage::helper('customer')->__('Suma LTL be PVM'),
            'index'     => 'm_total',
            'type'      => 'number',
            'align'     => 'right',
        ));
        $this->addColumn('m_vat', array(
            'header'    => Mage::helper('customer')->__('PVM Suma'),
            'index'     => 'm_vat',
            'type'      => 'number',
            'align'     => 'right',
        ));

        $this->addColumn('m_country', array(
            'header' => Mage::helper('sales')->__('Country'),
            'index' => 'm_country',
        ));

        $this->addColumn('state', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'state',
            'type' => 'options',
            'options' => Mage::getModel('sales/order_invoice')->getStates(),
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
        return $this;
    }
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('invoice_ids');
        $this->getMassactionBlock()->setUseSelectAll(false);

        $this->getMassactionBlock()->addItem('pdfinvoices_order_en', array(
             'label'=> Mage::helper('sales')->__('PDF Invoices (EN)'),
             'url'  => $this->getUrl('*/sales_invoice/pdfinvoicesEn'),
        ));
    	
        $this->getMassactionBlock()->addItem('pdfinvoices_order_lt', array(
             'label'=> Mage::helper('sales')->__('PDF Invoices (LT)'),
             'url'  => $this->getUrl('*/sales_invoice/pdfinvoicesLt'),
        ));

        if (Mage::getStoreConfigFlag('local_manadev/accounting/recalculate')) {
            $this->getMassactionBlock()->addItem('prepare_for_accounting', array(
                'label' => Mage::helper('sales')->__('Prepare for Accounting'),
                'url' => $this->getUrl('*/sales_invoice/prepareForAccounting'),
            ));

            $this->getMassactionBlock()->addItem('recalculate', array(
                'label' => Mage::helper('sales')->__('Recalculate'),
                'url' => $this->getUrl('*/sales_invoice/recalculate'),
            ));

        }

        return $this;
    }
}