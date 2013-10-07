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

}