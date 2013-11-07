<?php
/**
 * @category    Mana
 * @package     Local_Manadev
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * This block, when on page, initiates download of current downloadable product
 * @author Mana Team
 *
 */
class Local_Manadev_Block_Thankyou extends Mage_Core_Block_Template {
	/**
	 * Returns current product model from registry
	 * @return Mage_Catalog_Model_Product
	 */
	public function getProduct() {
		return Mage::registry('product');
	}
	public function getProductId() {
		return $this->getProduct()->getId();
	}
	public function getFileUrl() {
		return $this->getUrl('actions/product/file', array('_direct' => 'actions/product/file/id/'.$this->getProductId().'.zip'));
	}
	/**
	 * Add/remove blocks here and invoke methods of existing blocks. This one is called when this block is being 
	 * added to block tree, so it is crucial that this module defines all its dependencies in 
	 * etc/modules/<module name>.xml  
	 */
	protected function _prepareLayout() {
		// pass string translations and options (server side data which is constant to client-side script) 
		/* @var $js Mana_Core_Helper_Js */ $js = Mage::helper('mana_core/js');
		$js->options("#download-initiator", array(
			'fileUrl' => $this->getFileUrl(),
		));
	}
}