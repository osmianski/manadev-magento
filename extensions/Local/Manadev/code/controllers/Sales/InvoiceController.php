<?php

include_once 'app/code/core/Mage/Adminhtml/controllers/Sales/InvoiceController.php';

/**
 * Additional invoice actions
 * @author Mana Team
 *
 */
class Local_Manadev_Sales_InvoiceController extends Mage_Adminhtml_Sales_InvoiceController {
	public function prepareForAccountingAction() {
		$invoicesIds = $this->getRequest()->getPost('invoice_ids');
        if (!empty($invoicesIds)) {
            foreach (Mage::getResourceModel('sales/order_invoice_collection')
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('entity_id', array('in' => $invoicesIds))
                as $document) 
            {
            	Mage::helper('local_manadev')->prepareDocumentForAccounting($document);
            	$document->save();
            }
        }
		$this->_getSession()->addSuccess('Documents prepared successfully!');
		$this->_redirect('*/*/');
	}

    public function recalculateAction() {
        $invoicesIds = $this->getRequest()->getPost('invoice_ids');
        if (!empty($invoicesIds)) {
            foreach (Mage::getResourceModel('sales/order_invoice_collection')
                             ->addAttributeToSelect('*')
                             ->addAttributeToFilter('entity_id', array('in' => $invoicesIds))
                     as $document) {
                Mage::helper('local_manadev')->recalculateDocument($document);
                $document->save();
            }
        }
        $this->_getSession()->addSuccess('Documents recalculated successfully!');
        $this->_redirect('*/*/');
    }

    public function printEnAction()
    {
        if ($invoiceId = $this->getRequest()->getParam('invoice_id')) {
            if ($invoice = Mage::getModel('sales/order_invoice')->load($invoiceId)) {
                $pdf = Mage::getModel('local_manadev/pdf')
                	->setLanguage('en')
                	->setDocumentType('invoice')
                	->getPdf(array($invoice));
                $this->_prepareDownloadResponse($this->_filename($invoice, 'en'), $pdf->render(), 'application/pdf');
            }
        }
        else {
            $this->_forward('noRoute');
        }
    }
    public function printLtAction()
    {
        if ($invoiceId = $this->getRequest()->getParam('invoice_id')) {
            if ($invoice = Mage::getModel('sales/order_invoice')->load($invoiceId)) {
                $pdf = Mage::getModel('local_manadev/pdf')
                	->setLanguage('lt')
                	->setDocumentType('invoice')
                	->getPdf(array($invoice));
                $this->_prepareDownloadResponse($this->_filename($invoice, 'lt'), $pdf->render(), 'application/pdf');
            }
        }
        else {
            $this->_forward('noRoute');
        }
    }
    public function pdfinvoicesEnAction(){
        $invoicesIds = $this->getRequest()->getPost('invoice_ids');
        if (!empty($invoicesIds)) {
            $invoices = Mage::getResourceModel('sales/order_invoice_collection')
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('entity_id', array('in' => $invoicesIds))
                ->load();
            $pdf = Mage::getModel('local_manadev/pdf')
            	->setLanguage('en')
                ->setDocumentType('invoice')
                ->getPdf($invoices);

            return $this->_prepareDownloadResponse($this->_collectionFilename($invoices, 'en'), $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }
    public function pdfinvoicesLtAction(){
        $invoicesIds = $this->getRequest()->getPost('invoice_ids');
        if (!empty($invoicesIds)) {
            $invoices = Mage::getResourceModel('sales/order_invoice_collection')
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('entity_id', array('in' => $invoicesIds))
                ->load();
            $pdf = Mage::getModel('local_manadev/pdf')
            	->setLanguage('lt')
                ->setDocumentType('invoice')
                ->getPdf($invoices);

            return $this->_prepareDownloadResponse($this->_collectionFilename($invoices, 'lt'), $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }

    protected function _filename($document, $language) {
        return "invoice-{$document->getIncrementId()}-{$language}.pdf";
    }

    protected function _collectionFilename($documents, $language) {
        $no = array();
        foreach ($documents as $document) {
            $no[] = $document->getIncrementId();
            if (count($no) > 4) {
                $no[] = 'and-more';
                break;
            }
        }
        $no = implode('-', $no);
        return "invoice-{$no}-{$language}.pdf";
    }
}