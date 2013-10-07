<?php

include_once 'app/code/core/Mage/Adminhtml/controllers/Sales/CreditmemoController.php';

/**
 * Additional invoice actions
 * @author Mana Team
 *
 */
class Local_Manadev_Sales_CreditmemoController extends Mage_Adminhtml_Sales_CreditmemoController {
	public function prepareForAccountingAction() {
        $creditmemosIds = $this->getRequest()->getPost('creditmemo_ids');
        if (!empty($creditmemosIds)) {
            foreach (Mage::getResourceModel('sales/order_creditmemo_collection')
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('entity_id', array('in' => $creditmemosIds))
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
        $creditmemosIds = $this->getRequest()->getPost('creditmemo_ids');
        if (!empty($creditmemosIds)) {
            foreach (Mage::getResourceModel('sales/order_creditmemo_collection')
                             ->addAttributeToSelect('*')
                             ->addAttributeToFilter('entity_id', array('in' => $creditmemosIds))
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
        if ($creditmemoId = $this->getRequest()->getParam('creditmemo_id')) {
            if ($creditmemo = Mage::getModel('sales/order_creditmemo')->load($creditmemoId)) {
                $pdf = Mage::getModel('local_manadev/pdf')
                	->setLanguage('en')
                	->setDocumentType('creditmemo')
                	->getPdf(array($creditmemo));
                $this->_prepareDownloadResponse($this->_filename($creditmemo, 'en'), $pdf->render(), 'application/pdf');
            }
        }
        else {
            $this->_forward('noRoute');
        }
    }
    public function printLtAction()
    {
        if ($creditmemoId = $this->getRequest()->getParam('creditmemo_id')) {
            if ($creditmemo = Mage::getModel('sales/order_creditmemo')->load($creditmemoId)) {
                $pdf = Mage::getModel('local_manadev/pdf')
                	->setLanguage('lt')
                	->setDocumentType('creditmemo')
                	->getPdf(array($creditmemo));
                $this->_prepareDownloadResponse($this->_filename($creditmemo, 'lt'), $pdf->render(), 'application/pdf');
            }
        }
        else {
            $this->_forward('noRoute');
        }
    }
    public function pdfcreditmemosEnAction(){
        $creditmemosIds = $this->getRequest()->getPost('creditmemo_ids');
        if (!empty($creditmemosIds)) {
            $creditmemos = Mage::getResourceModel('sales/order_creditmemo_collection')
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('entity_id', array('in' => $creditmemosIds))
                ->load();
            $pdf = Mage::getModel('local_manadev/pdf')
            	->setLanguage('en')
                ->setDocumentType('creditmemo')
                ->getPdf($creditmemos);

            return $this->_prepareDownloadResponse($this->_collectionFilename($creditmemos, 'en'), $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }
    public function pdfcreditmemosLtAction(){
        $creditmemosIds = $this->getRequest()->getPost('creditmemo_ids');
        if (!empty($creditmemosIds)) {
            $creditmemos = Mage::getResourceModel('sales/order_creditmemo_collection')
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('entity_id', array('in' => $creditmemosIds))
                ->load();
            $pdf = Mage::getModel('local_manadev/pdf')
            	->setLanguage('lt')
                ->setDocumentType('creditmemo')
                ->getPdf($creditmemos);

            return $this->_prepareDownloadResponse($this->_collectionFilename($creditmemos, 'lt'), $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction() {
        if (!Mage::getStoreConfigFlag('local_manadev/accounting/delete_creditmemo')) {
            $this->_redirect('*/*/');
            return;
        }
        $creditmemosIds = $this->getRequest()->getPost('creditmemo_ids');
        if (!empty($creditmemosIds)) {
            foreach (Mage::getResourceModel('sales/order_creditmemo_collection')
                             ->addAttributeToSelect('*')
                             ->addAttributeToFilter('entity_id', array('in' => $creditmemosIds))
                     as $document) {
                /* @var $document Mage_Sales_Model_Order_Creditmemo */
                $order = $document->getOrder();
                /* @var $order Mage_Sales_Model_Order */

                foreach ($order->getItemsCollection() as $item) {

                    if ($item->getQtyRefunded() > 0) $item->setQtyRefunded(0)->save();
                }

                $order
                        ->setBaseDiscountRefunded(0)
                        ->setBaseShippingRefunded(0)
                        ->setBaseSubtotalRefunded(0)
                        ->setBaseTaxRefunded(0)
                        ->setBaseShippingTaxRefunded(0)
                        ->setBaseTotalOnlineRefunded(0)
                        ->setBaseTotalOfflineRefunded(0)
                        ->setBaseTotalRefunded(0)
                        ->setTotalOnlineRefunded(0)
                        ->setTotalOfflineRefunded(0)
                        ->setDiscountRefunded(0)
                        ->setShippingRefunded(0)
                        ->setShippingTaxRefunded(0)
                        ->setSubtotalRefunded(0)
                        ->setTaxRefunded(0)
                        ->setTotalRefunded(0);

                $state = 'processing';
                $status = 'complete';

                $order
                        ->setStatus($status)
                        ->setState($state)
                        ->save();

                $document->delete();
            }
        }
        $this->_getSession()->addSuccess('Documents deleted successfully!');
        $this->_redirect('*/*/');
    }

    protected function _filename($document, $language) {
        return "creditmemo-{$document->getIncrementId()}-{$language}.pdf";
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
        return "creditmemo-{$no}-{$language}.pdf";
    }

}