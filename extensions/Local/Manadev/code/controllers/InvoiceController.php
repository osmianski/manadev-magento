<?php

include_once 'app/code/core/Mage/Checkout/controllers/OnepageController.php';

class Local_Manadev_InvoiceController extends Mage_Core_Controller_Front_Action {
	public function printAction() {
        if ($invoice = $this->_invoice()) {
            $pdf = Mage::getModel('local_manadev/pdf')
                    ->setLanguage($invoice['language'])
                    ->setDocumentType('invoice')
                    ->getPdf(array($invoice['invoice']));
            $this->_prepareDownloadResponse($invoice['filename'], $pdf->render(), 'application/pdf');
        }
        else {
            $this->_forward('');
        }
    }

    protected function _invoice() {
        /* @var $session Mage_Customer_Model_Session */
        $session = Mage::getSingleton('customer/session');
        if (!$session->isLoggedIn()) {
            return false;
        }
        if (!($orderId = $this->getRequest()->getParam('order_id'))) {
            return false;
        }
        if (!($order = Mage::getModel('sales/order')->load($orderId))) {
            return false;
        }
        if (!$order->getId()) {
            return false;
        }
        if (!$order->getBillingAddress()->getCompany()) {
            return false;
        }

        /* @var $order Mage_Sales_Model_Order */
        foreach ($order->getInvoiceCollection() as $invoice) {
            /* @var $invoice Mage_Sales_Model_Order_Invoice */
            $language = $invoice->getBillingAddress()->getCountryId() == 'LT' ? 'lt' : 'en';
            $filename = "invoice-{$invoice->getIncrementId()}-{$language}.pdf";
            return compact('invoice', 'language', 'filename');
        }
        return false;
    }
}