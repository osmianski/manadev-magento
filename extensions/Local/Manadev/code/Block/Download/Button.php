<?php
/**
 * @category    Mana
 * @package     Local_Manadev
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * Contains initial data for download dialog 
 * @author Mana Team
 *
 */
class Local_Manadev_Block_Download_Button extends Mage_Core_Block_Template {
	public function setProductId($value) {
		$this->_productId = $value;

		// pass string translations and options (server side data which is constant to client-side script) 
		/* @var $js Mana_Core_Helper_Js */ $js = Mage::helper('mana_core/js');
		//$js->options(".btn-download for-product-".$this->getProductId(), array(
		//	'productName' => $this->getProductName(),
		//	'downloadUrl' => $this->getDownloadUrl(),
		//	'guestUrl' => $this->getGuestUrl(),
		//));
		return $this;
	} 
	protected $_productId;
	/**
	 * Returns current product model from registry
	 * @return Mage_Catalog_Model_Product
	 */
	public function getProduct() {
		if (!$this->_product) {
			if ($this->_productId) {
				$this->_product = Mage::getModel('catalog/product');
				$this->_product->setStoreId(Mage::app()->getStore()->getId());
				if (!is_numeric($this->_productId)) {
					$id = $this->_product->getIdBySku($this->_productId);
				}
				else {
					$id = $this->_productId;
				}
				$this->_product->load($id);
			}
			else {
				$this->_product = Mage::registry('product');
			}
		}
		return $this->_product;
	}
	public function setProduct($product) {
		$this->_product = $product;

		// pass string translations and options (server side data which is constant to client-side script) 
		/* @var $js Mana_Core_Helper_Js */ $js = Mage::helper('mana_core/js');
		//$js->options(".btn-download for-product-".$this->getProductId(), array(
		//	'productName' => $this->getProductName(),
		//	'downloadUrl' => $this->getDownloadUrl(),
		//	'guestUrl' => $this->getGuestUrl(),
		//));
		
		return $this;
	}
	
	/**
	 * Product with which current button is associated
	 * @var Mage_Catalog_Model_Product
	 */
	protected $_product;
	public function getProductName() {
		return $this->helper('catalog/output')
			->productAttribute($this->getProduct(), $this->getProduct()->getName(), 'name');
	}
	public function getProductId() {
		return $this->getProduct()->getId();
	}
	public function getDownloadUrl() {
		return $this->getUrl('actions/product/download', array('id' => $this->getProductId()));
	}
	public function getGuestUrl() {
		return $this->getUrl('actions/product/allowGuestDownload', array('id' => $this->getProductId()));
	}
	
	/**
	 * Add/remove blocks here and invoke methods of existing blocks. This one is called when this block is being 
	 * added to block tree, so it is crucial that this module defines all its dependencies in 
	 * etc/modules/<module name>.xml  
	 */
	protected function _prepareLayout() {
		if ($this->getProduct()) {
			// pass string translations and options (server side data which is constant to client-side script) 
			/* @var $js Mana_Core_Helper_Js */ $js = Mage::helper('mana_core/js');
			//$js->options(".btn-download for-product-".$this->getProductId(), array(
			//	'productName' => $this->getProductName(),
			//	'downloadUrl' => $this->getDownloadUrl(),
			//	'guestUrl' => $this->getGuestUrl(),
			//));
		}
	} 
	
	protected function _afterToHtml($html) {
		/* @var $js Mana_Core_Helper_Js */ $js = Mage::helper('mana_core/js');
		$html .= $js->optionsToHtml(".btn-download for-product-".$this->getProductId(), array(
			'productName' => $this->getProductName(),
			'downloadUrl' => $this->getDownloadUrl(),
			'guestUrl' => $this->getGuestUrl(),
		));
		return $html;
	}
}