<?php
/**
 * @category    Mana
 * @package     Mana_ProductLists
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* BASED ON SNIPPET: Models/Observer */
/**
 * This class observes certain (defined in etc/config.xml) events in the whole system and provides public methods - handlers for
 * these events.
 * @author Mana Team
 *
 */
class Mana_ProductLists_Model_Observer {
	/* BASED ON SNIPPET: Models/Event handler */
	/**
	 * Adds new tab to product editing page (handles event "core_block_abstract_prepare_layout_after")
	 * @param Varien_Event_Observer $observer
	 */
	public function addProductTabs($observer) {
		/* @var $block Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs */ $block = $observer->getEvent()->getBlock();
		/* @var $core Mana_Core_Helper_Data */ $core = Mage::helper(strtolower('Mana_Core'));
		if ($block instanceof Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs) {
            $product = $block->getProduct();
            if ($product->getAttributeSetId()) {
                foreach ($core->getSortedXmlChildren(Mage::getConfig()->getNode('mana_productlists'), 'types') as $key => $options) {
                    if (!Mage::getStoreConfigFlag('mana_productlists/'.$key.'/is_enabled')) {
                        continue;
                    }
                    if (isset($options->product_types) && !in_array($block->getProduct()->getTypeId(), explode(',', (string)$options->product_types))) {
                        continue;
                    }

                    $block->addTab($key.'_links', array(
                        'label'     => (string)$options->tab_title,
                        'url'       => $block->getUrl((string)$options->tab_action, array('_current' => true)),
                        'class'     => 'ajax',
                        'after'		=> 'categories',
                    ));
                }
            }
			
		}
	}
	/* BASED ON SNIPPET: Models/Event handler */
	/**
	 * Similar to other links, deserializes grid data (handles event "catalog_product_prepare_save")
	 * @param Varien_Event_Observer $observer
	 */
	public function deserializeProductLinks($observer) {
		/* @var $product Mage_Catalog_Model_Product */ $product = $observer->getEvent()->getProduct();
		/* @var $request Mage_Core_Controller_Request_Http */ $request = $observer->getEvent()->getRequest();
		/* @var $core Mana_Core_Helper_Data */ $core = Mage::helper(strtolower('Mana_Core'));
		
        $links = $request->getPost('links');
        foreach ($core->getSortedXmlChildren(Mage::getConfig()->getNode('mana_productlists'), 'types') as $key => $options) {
	        if (isset($links[$key])) {
	            $product->setData($key.'_link_data', Mage::helper('mana_productlists')->decodeGridSerializedInput($links[$key]));
	        }
        }
	}
	/* BASED ON SNIPPET: Models/Event handler */
	/**
	 * REPLACE THIS WITH DESCRIPTION (handles event "catalog_product_save_after")
	 * @param Varien_Event_Observer $observer
	 */
	public function validateProductLinks($observer) {
		/* @var $product Mage_Catalog_Model_Product */ $product = $observer->getEvent()->getProduct();
        /* @var $linkModel Mage_Catalog_Model_Product_Link */ $linkModel = $product->getLinkInstance();
		/* @var $core Mana_Core_Helper_Data */ $core = Mage::helper(strtolower('Mana_Core'));
		$errors = array();
		
		// here is the hack for dumb validate action which does not provide a way to inject POST parsing code through 
		// dedicated event
		if (Mage::app()->getRequest() && Mage::app()->getRequest()->getControllerName() == 'catalog_product' && 
			Mage::app()->getRequest()->getActionName() == 'validate' && Mage::app()->getRequest()->getModuleName() == 'admin')
		{
	        $links = Mage::app()->getRequest()->getPost('links');
	        foreach ($core->getSortedXmlChildren(Mage::getConfig()->getNode('mana_productlists'), 'types') as $key => $options) {
		        if (isset($links[$key])) {
		            $product->setData($key.'_link_data', Mage::helper('mana_productlists')->decodeGridSerializedInput($links[$key]));
		        }
	        }
		}
		else {
			if (!Mage::registry('current_product') || Mage::registry('current_product') != $observer->getEvent()->getProduct()->getId()) {
				// do not handle nonUI (or recursive) changes
				return;
			}
		}
		
	    foreach ($core->getSortedXmlChildren(Mage::getConfig()->getNode('mana_productlists'), 'types') as $key => $options) {
			if (!is_null($product->getData($key.'_link_data')) && isset($options->validate_method)) {
				list($model, $method) = explode('::', (string)$options->validate_method);
				$model = Mage::getSingleton($model);
	        	foreach ($product->getData($key.'_link_data') as $id => $fields) {
	        		$model->$method($errors, $product, $id, $fields, $key, $options);
	        	}
	        }
	    }
        if (count($errors) > 0) {
        	throw new Mage_Core_Exception(implode('<br />', $errors));
        }
	}
	/* BASED ON SNIPPET: Models/Event handler */
	/**
	 * Similar to other links, updates database (handles event "catalog_product_save_after")
	 * @param Varien_Event_Observer $observer
	 */
	public function saveProductLinks($observer) {
		if (!($product = Mage::registry('current_product')) || $product->getId() != $observer->getEvent()->getProduct()->getId()) {
			// do not handle nonUI (or recursive) changes
			return;
		}
		
		/* @var $core Mana_Core_Helper_Data */ $core = Mage::helper(strtolower('Mana_Core'));
		/* @var $product Mage_Catalog_Model_Product */ $product = $observer->getEvent()->getProduct();
        /* @var $linkModel Mage_Catalog_Model_Product_Link */ $linkModel = $product->getLinkInstance();
		
	    foreach ($core->getSortedXmlChildren(Mage::getConfig()->getNode('mana_productlists'), 'types') as $key => $options) {
	    	if (!is_null($product->getData($key.'_link_data'))) {
	    		list($model, $method) = explode('::', (string)$options->before_save_method);
	    		$model = Mage::getSingleton($model);
	    		$product->setData($key.'_link_data', $model->$method($product, $product->getData($key.'_link_data'), $options));
	    		$collection = Mage::getResourceModel((string)$options->collection_resource);
				$linkModel->getResource()->saveProductLinks($product, $product->getData($key.'_link_data'), $collection->getLinkTypeId());
	    	}
	    }
	}
	
}