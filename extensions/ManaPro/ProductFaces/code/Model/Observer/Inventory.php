<?php
/**
 * @category    Mana
 * @package     ManaPro_ProductFaces
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * This class contains inventory sinchronization logic
 * @author Mana Team
 *
 */
class ManaPro_ProductFaces_Model_Observer_Inventory {
	/* BASED ON SNIPPET: Models/Event handler */
	/**
	 * handles event "sales_model_service_quote_submit_before"
	 * @param Varien_Event_Observer $observer
	 */
	public function updateFromQuote($observer) {
		/* @var $resource ManaPro_ProductFaces_Resource_Inventory */ $resource = Mage::getResourceModel('manapro_productfaces/inventory');
        /* @var $quote Mage_Sales_Model_Quote */ $quote = $observer->getEvent()->getQuote();
		$items = array();
        foreach ($quote->getItemsCollection() as /* @var $item Mage_Sales_Model_Quote_Item */ $item) {
        	$items[$item->getProductId()] = $item->getQty();
        }        
        $resource->updateRepresentedProducts($items, -1);
	}
	/* BASED ON SNIPPET: Models/Event handler */
	/**
	 * handles event "sales_model_service_quote_submit_failure"
	 * @param Varien_Event_Observer $observer
	 */
	public function restoreFromQuote($observer) {
		/* @var $resource ManaPro_ProductFaces_Resource_Inventory */ $resource = Mage::getResourceModel('manapro_productfaces/inventory');
        /* @var $quote Mage_Sales_Model_Quote */ $quote = $observer->getEvent()->getQuote();
		$items = array();
        foreach ($quote->getItemsCollection() as /* @var $item Mage_Sales_Model_Quote_Item */ $item) {
        	$items[$item->getProductId()] = $item->getQty();
        }        
        $resource->updateRepresentedProducts($items, 1);
	}
	/* BASED ON SNIPPET: Models/Event handler */
	/**
	 * handles event "sales_order_item_cancel"
	 * @param Varien_Event_Observer $observer
	 */
	public function updateFromOrderItem($observer) {
		/* @var $resource ManaPro_ProductFaces_Resource_Inventory */ $resource = Mage::getResourceModel('manapro_productfaces/inventory');
        /* @var $item Mage_Sales_Model_Order_Item */ $item = $observer->getEvent()->getItem();
        $resource->updateRepresentedProduct($item->getProductId());
	}
	/* BASED ON SNIPPET: Models/Event handler */
	/**
	 * handles event "sales_order_creditmemo_save_after"
	 * @param Varien_Event_Observer $observer
	 */
	public function updateFromCreditMemo($observer) {
		/* @var $resource ManaPro_ProductFaces_Resource_Inventory */ $resource = Mage::getResourceModel('manapro_productfaces/inventory');
		/* @var $creditmemo Mage_Sales_Model_Order_Creditmemo */ $creditmemo = $observer->getEvent()->getCreditmemo();
		$items = array();
        foreach ($creditmemo->getItemsCollection() as /* @var $item Mage_Sales_Model_Order_Creditmemo_Item */ $item) {
        	$items[$item->getProductId()] = $item->getQty();
        }        
        $resource->updateRepresentedProducts($items, 1);
	}
	/* BASED ON SNIPPET: Models/Event handler */
	/**
	 * handles event "admin_system_config_changed_section_cataloginventory"
	 * @param Varien_Event_Observer $observer
	 */
	public function updateAll($observer) {
		/* @var $resource ManaPro_ProductFaces_Resource_Inventory */ $resource = Mage::getResourceModel('manapro_productfaces/inventory');
		$resource->updateAll();
	}

	public function createDropTrigger($observer) {
        Mage::getSingleton('manapro_productfaces/changeLog')->createDropTriggerAsConfigured();
	}

	public function enterpriseFlatColumns($observer) {
        $columnsObject = $observer->getEvent()->getColumns();
        $columns = [];
        foreach ($columnsObject->getData('columns') as $key => $column) {
            if ($key == 'm_represented_qty') {
                continue;
            }

            $columns[$key] = $column;
        }

        $columnsObject->setData('columns', $columns);
	}
}