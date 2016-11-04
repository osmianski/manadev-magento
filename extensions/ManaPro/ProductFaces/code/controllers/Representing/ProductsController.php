<?php
/**
 * @category    Mana
 * @package     ManaPro_ProductFaces
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Provides additional action for representing products tab in product editor
 * @author Mana Team
 *
 */

require_once  BP.DS.'app'.DS.'code'.DS.'core'.DS.'Mage'.DS.'Adminhtml'.DS.'controllers'.DS.'Catalog'.DS.'ProductController.php';

class ManaPro_ProductFaces_Representing_ProductsController extends Mage_Adminhtml_Catalog_ProductController {
	/**
	 * AJAX action, renders initial tab markup
	 */
	public function tabAction() {
		/* @var $helper ManaPro_ProductFaces_Helper_Data */ $helper = Mage::helper(strtolower('ManaPro_ProductFaces'));
        $product = $this->_initProduct();
        if (($representedProduct = $helper->getRepresentedProduct($product)) && $representedProduct->getId() != $product->getId()) {
        	// currently viewed product represents other product, so merely show link to "parent" product and basic
        	// info (qty representation conditions and current qty represented)
        	$this->loadLayout('adminhtml_representing_products_tab_represented');
	        $this->getLayout()->getBlock('catalog.product.edit.tab.m_represented')
	            ->setProductMRepresented($representedProduct);
        }
        else {
        	// currently viewed product does not represent any other product and hence may be itself 
        	// represented by other products (or already is)
        	$this->loadLayout('adminhtml_representing_products_tab_representing');
	        $this->getLayout()->getBlock('catalog.product.edit.tab.m_representing')
	            ->setProductsMRepresenting($this->getRequest()->getPost('products_m_representing', null));

	        for ($i = 0; $i < 10; $i++) {
	        	if ($attributeCode = Mage::getStoreConfig('manapro_productfaces/quick_edit/attribute'.$i, $product->getStoreId())) {
	        		$attributes = Mage::getResourceModel('catalog/product_attribute_collection');
	        		$attributes->getSelect()->where('attribute_code = ?', $attributeCode);
	        		foreach ($attributes as $attribute) {
				        switch ($attribute->getFrontend()->getInputType()) {
				        	case 'price': 
				        	case 'text': 
				        	case 'select':
						        $this->getLayout()->getBlock('m_representing_grid_serializer')
						        	->addColumnInputName($attributeCode);
				        		break;
				        }
	        		}
	        	}
	        }
        }
        $this->renderLayout();
	}
	/**
	 * AJAX action, updates product grid on some grid action (filter, sorting)
	 */
	public function gridAction() {
        $product = $this->_initProduct();
        $this->loadLayout();
        
        $clientData = $this->getRequest()->getPost('products_m_representing', null);
        $clientData = $this->_decodeGridSerializedInput($clientData[0]);
        
        $this->getLayout()->getBlock('catalog.product.edit.tab.m_representing')
            ->setProductsMRepresenting($clientData);
        $this->renderLayout();
	}
	public function addCopyAction() {
        $product = $this->_initProduct();
		/* @var $newProduct Mage_Catalog_Model_Product */ $newProduct = $product->duplicate();
        $newProduct = Mage::getModel('catalog/product')->setStoreId(0)->load($newProduct->getId());
        $newProduct
        	->setImage($product->getImage())
        	->setSmallImage($product->getSmallImage())
        	->setThumbnail($product->getThumbnailImage());
        
        Mage::register('m_product_copy', $newProduct);
        $i = 0;
        $newSku = '';
        $suffix = Mage::getStoreConfig('manapro_productfaces/cloning/sku_suffix');
        /* @var $productModel Mage_Catalog_Product_Model */ $productModel = Mage::getModel('catalog/product');
        while (!$newSku) {
        	$sku = $product->getSku().$suffix.(++$i);
        	if (!$productModel->getIdBySku($sku)) {
        		$newSku = $sku;
        	}
        }
        $newProduct->setSku($newSku);
        $newProduct->setStatus(Mage::getStoreConfig('manapro_productfaces/cloning/status'));

        for ($i = 0; $i < 10; $i++) {
        	if ($attributeCode = Mage::getStoreConfig('manapro_productfaces/not_cloned/attribute'.$i)) {
        		$attributes = Mage::getResourceModel('catalog/product_attribute_collection');
        		$attributes->getSelect()->where('attribute_code = ?', $attributeCode);
        		foreach ($attributes as $attribute) {
			        $newProduct->setData($attributeCode, $attribute->getDefaultValue());
        		}
        	}
        }
        $newProduct->save();
        
        $this->loadLayout();
        $this->getLayout()->getBlock('catalog.product.edit.tab.m_representing')
            ->setProductsMRepresenting($this->getRequest()->getPost('products_m_representing', null));

        $response = new Varien_Object();
        for ($i = 0; $i < 10; $i++) {
        	if ($attributeCode = Mage::getStoreConfig('manapro_productfaces/quick_edit/attribute'.$i, $product->getStoreId())) {
        		$attributes = Mage::getResourceModel('catalog/product_attribute_collection');
        		$attributes->getSelect()->where('attribute_code = ?', $attributeCode);
        		foreach ($attributes as $attribute) {
			        switch ($attribute->getFrontend()->getInputType()) {
			        	case 'price': 
			        	case 'text': 
			        	case 'select':
			        		$response->setData($attributeCode, $newProduct->getData($attributeCode));
			        		break;
			        }
        		}
        	}
        }
            
        $response
        	->setProductId($newProduct->getId())
        	->setOutput($this->getLayout()->setDirectOutput(false)->getOutput());
        $this->getResponse()->setBody($response->toJson());
	}
	public function updateAction() {
        $response = Mage::getResourceModel('manapro_productfaces/inventory')->calculateQuantities(
        	json_decode($this->getRequest()->getParam('productData'), true), 
        	json_decode($this->getRequest()->getParam('representingProductData'), true)
        );
        $this->loadLayout();
	    $this->_writeMessages($response['messages'], $this->getLayout()->getBlock('m_representing_messages'));
        $response['message_html'] = $this->getLayout()->getOutput();
		$this->getResponse()->setBody(json_encode($response));
	}
	protected function _writeMessages($messages, $block) {
      	foreach ($messages  as $message) {
      		$method = 'add'.$message['type'];
      		$text = $message['text'];
      		if (isset($message['option']) && $message['option']) {
      			$text .= '<a href="#" onclick="m_hideProductFacesWarning(\''.$message['option'].'\')">'.$this->__('Hide this warning').'</a>';
      		}
	        $block->$method($text);
       	}
	}
	public function hideWarningAction() {
		if ($option = $this->getRequest()->getParam('option')) {
			/* @var $config Mage_Core_Model_Config_data */ $config = Mage::getModel('core/config_data');
			$config->load($option, 'path');
			$config->setValue(0)->save();
			Mage::app()->cleanCache();
		}
	}
    protected function _decodeGridSerializedInput($encoded)
    {
        return Mage::helper('manapro_productfaces')->decodeGridSerializedInput($encoded);
    }
	public function productDataAction() {
		/* @var $core Mana_Core_Helper_Data */ $core = Mage::helper(strtolower('Mana_Core'));
		$ids = $this->getRequest()->getParam('selected_ids');
		$storeId = $this->getRequest()->getParam('store', 0);
		$data = array();
		foreach ($ids as $id) {	
			$response = array(
				'entity_id' => $id,
				'm_unit' => Mage::getStoreConfig('manapro_productfaces/default_values/unit_of_measure'),
				'm_parts' => Mage::getStoreConfig('manapro_productfaces/default_values/parts'),
				'position' => Mage::getStoreConfig('manapro_productfaces/default_values/position'),
                'm_pack_qty' => Mage::getStoreConfig('manapro_productfaces/default_values/pack_qty'),
			);
			if ($product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($id)) {
				$response['sku'] = $product->getSku();
				$response['name'] = $product->getName();
				for ($i = 0; $i < 10; $i++) {
			    	if ($attributeCode = Mage::getStoreConfig('manapro_productfaces/quick_edit/attribute'.$i, $product->getStoreId())) {
			        	$attributes = Mage::getResourceModel('catalog/product_attribute_collection');
			        	$attributes->getSelect()->where('attribute_code = ?', $attributeCode);
			        	foreach ($attributes as $attribute) {
							switch ($attribute->getFrontend()->getInputType()) {
						    	case 'price': 
						        	$response[$attributeCode] = sprintf("%1.2f", $product->getData($attributeCode));
						        	break;
						    	case 'text': 
						        case 'select':
						        	$response[$attributeCode] = $product->getData($attributeCode);
						        	break;
						    }
		        		}
		        	}
		        }	
			}
			$data[] = $response;
		}
		$this->getResponse()->setBody(json_encode($data));
	}
    public function chooserAction()
    {
        $this->getResponse()->setBody(Mage::helper('mana_admin')->getProductChooserHtml());
    }

    public function processInventoryChangeLogAction() {
        /* @var ManaPro_ProductFaces_Model_ChangeLog $changeLog */
        $changeLog = Mage::getSingleton('manapro_productfaces/changeLog');

        try {
            if ($count = $changeLog->runManually()) {
                $this->_getSession()->addSuccess($this->__('%s pending inventory change(s) were processed.', $count));
            }
            else {
                $this->_getSession()->addSuccess($this->__('All inventory is up to date, there is nothing to process.'));
            }

        }
        catch (Exception $e) {
                $this->_getSession()->addError($this->__('There was an error while processing inventory changes: %s',
                    $e->getMessage()));
        }
        $this->_redirect('adminhtml/system_config/edit', array('section' => 'manapro_productfaces'));
    }

	protected function _isAllowed() {
		return true;
	}
}