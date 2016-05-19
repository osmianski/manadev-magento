<?php

/* BASED ON SNIPPET: Models/Observer */
/**
 * This class observes certain (defined in etc/config.xml) events in the whole system and provides public methods - handlers for
 * these events.
 * @author Mana Team
 *
 */
class Local_Manadev_Model_Observer {
	/* BASED ON SNIPPET: Models/Event handler */
	/**
	 * Adds amounts needed for accounting (handles event "sales_order_invoice_save_before")
	 * @param Varien_Event_Observer $observer
	 */
	public function prepareInvoiceForAccounting($observer) {
		/* @var $object Mage_Sales_Model_Order_Invoice */ $object = $observer->getEvent()->getDataObject();
		
		if (!$object->getMDate()) {
			Mage::helper('local_manadev')->prepareDocumentForAccounting($object);
		}

        foreach($object->getAllItems() as $invoiceItem) {
		    $sku = Mage::getModel('catalog/product')->load($invoiceItem->getProductId())->getSku();
		    if($sku == Mage::getStoreConfig('local_manadev/support_services_sku')) {
                $buyRequest = $invoiceItem->getOrderItem()->getProductOptionByCode('info_buyRequest');
                if(!isset($buyRequest['m_license'])) {
                    continue;
                }

                $item_id = $buyRequest['m_license'];
                $item = Mage::getModel('downloadable/link_purchased_item')->load($item_id);

                $new_date = !in_array($item->getStatus(), array(Local_Manadev_Model_Download_Status::M_LINK_STATUS_AVAILABLE,
                        Local_Manadev_Model_Download_Status::M_LINK_STATUS_AVAILABLE_TIL)) ?
                        date('Y-m-d', Varien_Date::toTimestamp($object->getOrder()->getCreatedAt())) :
                        $item->getData('m_support_valid_til');

                for($x = 0; $x<$invoiceItem->getQty(); $x++) {
                    $new_date = strtotime("+6 months", Varien_Date::toTimestamp($new_date));
                    $new_date = date("Y-m-d", $new_date);
                }
                $item->setData('m_support_valid_til', $new_date)
                    ->save();
		    }
		}
	}
	
	/* BASED ON SNIPPET: Models/Event handler */
	/**
	 * Adds amounts needed for accounting (handles event "sales_order_creditmemo_save_before")
	 * @param Varien_Event_Observer $observer
	 */
	public function prepareCreditmemoForAccounting($observer) {
		/* @var $object Mage_Sales_Model_Order_Creditmemo */ $object = $observer->getEvent()->getDataObject();
		
		if (!$object->getMDate()) {
			Mage::helper('local_manadev')->prepareDocumentForAccounting($object);
		}

		foreach($object->getAllItems() as $invoiceItem) {
		    $sku = Mage::getModel('catalog/product')->load($invoiceItem->getProductId())->getSku();
		    if($sku == Mage::getStoreConfig('local_manadev/support_services_sku')) {
                $buyRequest = $invoiceItem->getOrderItem()->getProductOptionByCode('info_buyRequest');
                if(!isset($buyRequest['m_license'])) {
                    continue;
                }

                $item_id = $buyRequest['m_license'];
                $item = Mage::getModel('downloadable/link_purchased_item')->load($item_id);

                $new_date = $item->getData('m_support_valid_til');
                for($x = 0; $x<$invoiceItem->getQty(); $x++) {
                    $new_date = strtotime("-6 months", strtotime($new_date));
                    $new_date = date("Y-m-d", $new_date);
                }
                $item->setData('m_support_valid_til', $new_date)
                    ->save();
		    }
		}
	}

    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "adminhtml_block_html_before")
     * @param Varien_Event_Observer $observer
     */
    public function extendBackendForms($observer) {
        /* @var $block Mage_Adminhtml_Block_Template */ $block = $observer->getEvent()->getBlock();

    	if ($block instanceof Mage_Adminhtml_Block_Tax_Class_Edit_Form) {
    	    /* @var $form Varien_Data_Form */
    	    $form = $block->getForm();
    	    /* @var $fieldset Varien_Data_Form_Element_Fieldset*/
    	    $fieldset = $form->getElement('base_fieldset');
            $model = Mage::registry('tax_class');

            $fieldset->addField('auto_assign_condition', 'textarea', array(
                    'name' => 'auto_assign_condition',
                    'label' => Mage::helper('local_manadev')->__('Auto Assign Condition'),
                    'value' => $model->getAutoAssignCondition(),
                )
            );

        }
        elseif ($block instanceof Mage_Adminhtml_Block_Customer_Group_Edit_Form) {
            /* @var $form Varien_Data_Form */
            $form = $block->getForm();
            /* @var $fieldset Varien_Data_Form_Element_Fieldset*/
            $fieldset = $form->getElement('base_fieldset');
            $model = Mage::registry('current_group');

            $fieldset->addField('tax_independent_code', 'text', array(
                    'name' => 'tax_independent_code',
                    'label' => Mage::helper('local_manadev')->__('Tax Independent Code'),
                    'value' => $model->getTaxIndependentCode(),
                )
            );
        }
    }

    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "controller_action_layout_render_before_checkout_index_index",
     * "controller_action_layout_render_before_paypal_express_review")
     * @param Varien_Event_Observer $observer
     */
    public function addCheckoutOptions($observer) {
        Mage::helper('mana_core/js')->options('.m-checkout', array(
            'updateOrderUrl' => Mage::getUrl('actions/checkout/updateOrderDetails', array(
                '_secure' => Mage::app()->getFrontController()->getRequest()->isSecure(),
            )),
        ));
    }

    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "m_ajax_response")
     * @param Varien_Event_Observer $observer
     */
    public function renderAjaxResponse($observer) {
        /* @var $action string */
        $action = $observer->getEvent()->getAction();
        /* @var $response Varien_Object */
        $response = $observer->getEvent()->getResponse();

        if ($action == 'update' && Mage::helper('mana_core')->getRoutePath() == 'checkout/index/index') {
            if (($vat = Mage::app()->getRequest()->getParam('vat', false)) !== false) {
                $data = Mage::getSingleton('core/layout')->getBlock('m_ajax_update')->toAjaxHtml($action);
                switch (Mage::getSingleton('checkout/session')->getMIsVatValid()) {
                    case Mana_Vat_Helper_Data::NON_EU:
                        $data['vat'] = array('na' => true);
                        break;
                    case Mana_Vat_Helper_Data::INVALID:
                        $data['vat'] = array('error' => Mage::helper('local_manadev')->__('Invalid VAT number'));
                        break;
                    case Mana_Vat_Helper_Data::VALID:
                        $data['vat'] = array('success' => true);
                        break;
                }
                $response->setData($data);
                $response->setIsHandled(true);
            }
        }
    }

    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "m_ajax_request")
     * @param Varien_Event_Observer $observer
     */
    public function prepareAjaxRequest($observer) {
        if (Mage::registry('m_current_ajax_action') == 'update' && Mage::helper('mana_core')->getRoutePath() == 'checkout/index/index') {
            if (($vat = Mage::app()->getRequest()->getParam('vat', false)) !== false) {
                Mage::getSingleton('checkout/session')->setMVat($vat);
                Mage::getSingleton('checkout/session')->setMIsVatValid(Mage::helper('mana_vat')->validateVat($vat));
            }
            Mage::getSingleton('checkout/session')->setMCountryId(Mage::app()->getRequest()->getParam('country'));
        }
    }

    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "controller_action_predispatch_adminhtml_customer_group_save")
     * @param Varien_Event_Observer $observer
     */
    public function saveIndependentTaxCode($observer) {
        /* @var $controllerAction Mage_Adminhtml_Customer_GroupController */
        $controllerAction = $observer->getEvent()->getControllerAction();

        $customerGroup = Mage::getModel('customer/group');
        $id = $controllerAction->getRequest()->getParam('id');
        if (!is_null($id)) {
            $customerGroup->load($id);
        }

        if ($taxClass = $controllerAction->getRequest()->getParam('tax_class')) {
            try {
                $customerGroup->setCode($controllerAction->getRequest()->getParam('code'))
                        ->setTaxIndependentCode($controllerAction->getRequest()->getParam('tax_independent_code'))
                        ->setTaxClassId($taxClass)
                        ->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('customer')->__('The customer group has been saved.'));
                $controllerAction->getResponse()->setRedirect($controllerAction->getUrl('*/customer_group'));
                if (is_null($id)) {
                    $_POST['id'] = $customerGroup->getId();
                }
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setCustomerGroupData($customerGroup->getData());
                $controllerAction->getResponse()->setRedirect($controllerAction->getUrl('*/customer_group/edit', array('id' => $id)));
                return;
            }
        } else {
            $this->_forward('new');
        }
    }

    /**
     * Handles event "controller_action_layout_generate_blocks_after".
     * @param Varien_Event_Observer $observer
     */
    public function renderMessages($observer) {
        /* @var $action Mage_Core_Controller_Varien_Action */
        $action = $observer->getEvent()->getData('action');
        if ($action instanceof Mage_Paypal_Controller_Express_Abstract) {
            Mage::helper('mana_core')->initLayoutMessages('customer/session');
            Mage::getSingleton('core/layout')->getBlock('head')->setTitle(Mage::helper('mana_core')->__('Review Your Order'));
        }

        // initiate product download if there is a flag in session to do so
        if ($pendingDownloadProductId = $this->_getCustomerSession()->getData('pending_download_product_id')) {
            /* @var $js Mana_Core_Helper_Js */ $js = Mage::helper('mana_core/js');
            $js->options("#download-initiator", array(
                'fileUrl' => Mage::getUrl('actions/product/file', array('_direct' => 'actions/product/file/id/'. $pendingDownloadProductId.'.zip')),
            ));
            $this->_getCustomerSession()->unsetData('pending_download_product_id');
        }

        if($pendingDownloadLinkHash = $this->_getCustomerSession()->getData('m_pending_download_link_hash')) {
            $block = Mage::getSingleton('core/layout')->addBlock('Local_Manadev_Block_Download', 'download');
            $block->setData('link_hash', $pendingDownloadLinkHash);
            Mage::getSingleton('core/layout')->getBlock('content')->insert($block);
            $this->_getCustomerSession()->unsetData('m_pending_download_link_hash');
        }
    }

    public function setLinkStatus($observer) {
        $order = $observer->getEvent()->getOrder();

        if (!$order->getId()) {
            //order not saved in the database
            return $this;
        }

        $downloadableItemsId = [];
        foreach ($order->getAllItems() as $item) {
            if ($item->getProductType() == Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE
                || $item->getRealProductType() == Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE
            ) {
                $downloadableItemsId[] = $item->getId();
            }
        }

        if($downloadableItemsId) {
            $linkPurchased = Mage::getResourceModel('downloadable/link_purchased_item_collection')
                ->addFieldToFilter('order_item_id', array('in' => $downloadableItemsId));
            foreach ($linkPurchased as $link) {
                if($link->getStatus() == Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_AVAILABLE
                    && trim($link->getData('m_registered_domain')) == ""
                ) {
                    $link->setStatus(Local_Manadev_Model_Download_Status::M_LINK_STATUS_NOT_REGISTERED)
                    ->save();
                }
            }
        }

        return $this;
    }

    protected function _getCustomerSession() {
        return Mage::getSingleton('customer/session');
    }
}