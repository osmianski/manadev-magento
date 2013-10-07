<?php

class Local_Manadev_Sales_BilltoController extends Mage_Adminhtml_Controller_Action {
	public function editAction() {
		Mage::register('redirect_to', $this->getRequest()->getParam('redirect_to'));
		if ($documentId = $this->getRequest()->getParam('invoice_id')) {
			$documentType = 'sales/order_invoice';
			$documentTypeName = 'Invoice';
		}
		elseif ($documentId = $this->getRequest()->getParam('creditmemo_id')) {
			$documentType = 'sales/order_creditmemo';
			$documentTypeName = 'Credit Memo';
		}
		else {
			throw new Exception('Not implemented');
		}
		
		$document = Mage::getModel($documentType)->load($documentId);
		Mage::register('document_type', $documentType);
		Mage::register('document_type_name', $documentTypeName);
		Mage::register('document', $document);
		
		$addressId = $document->getBillingAddressId();
		$address = Mage::getModel('sales/order_address')->load($addressId);
		Mage::register('address', $address);
		
		$orderId = $address->getParentId();
		$order = Mage::getModel('sales/order')->load($orderId);
		Mage::register('order', $order);
		
		$this->_title('Edit Billing Address');
		$this->loadLayout();
		$this->renderLayout();
	}
	public function saveAction() {
		$address = Mage::getModel('sales/order_address')->load($this->getRequest()->getParam('entity_id'));
		$address->addData($this->getRequest()->getPost())->save();
		$this->_getSession()->addSuccess('Address information successfully updated!');
		$this->getResponse()->setRedirect(Mage::helper('core')->urlDecode($this->getRequest()->getParam('redirect_to')));
	}
}