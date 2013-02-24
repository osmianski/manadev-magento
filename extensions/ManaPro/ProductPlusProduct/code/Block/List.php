<?php
/**
 * @category    Mana
 * @package     ManaPro_ProductPlusProduct
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Enter description here ...
 * @author Mana Team
 *
 */
class ManaPro_ProductPlusProduct_Block_List extends Mana_ProductLists_Block_List {
    protected function _getCollectionType() {
    	return 'manapro_productplusproduct/collection';
    }
	protected function _getLinkType() {
		return ManaPro_ProductPlusProduct_Resource_Setup::LINK_TYPE;
	}
	public function addToCartUrl() {
		return $this->_addToUrl('cart');
	}
	public function addToWishlistUrl() {
		return $this->_addToUrl('wishlist');
	}
	public function addToCompareUrl() {
		return $this->_addToUrl('compare');
	}
	protected function _addToUrl($action) {
		return Mage::getUrl('m-bought-together/addto/'.$action, array(
			Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => Mage::helper('core/url')->getEncodedUrl()
		));
	}
    protected function _beforeToHtml()
    {
    	parent::_beforeToHtml();
		$prices = array();
        foreach ($this->_items as $product) {
            $prices[$product->getId()] = $product->getFinalPrice();
        }

		Mage::helper('mana_core/js')->options('#m-bought-together', array(
			'prices' => $prices,
			'pricelabels' => array(
				$this->__('Price for All'), 
				$this->__('Price'),
				$this->__('Price For Both'),
				$this->__('Price For All Three'),
				$this->__('Price For All Four'),
				$this->__('Price For All Five'),
				$this->__('Price For All Six'),
				$this->__('Price For All Seven'),
				$this->__('Price For All Eight'),
				$this->__('Price For All Nine'),
				$this->__('Price For All Ten'),
			),
			'addToCartLabels' => array(
				$this->__('Add all to Cart'), 
				$this->__('Add to Cart'),
				$this->__('Add both to Cart'),
				$this->__('Add all three to Cart'),
				$this->__('Add all four to Cart'),
				$this->__('Add all five to Cart'),
				$this->__('Add all six to Cart'),
				$this->__('Add all seven to Cart'),
				$this->__('Add all eight to Cart'),
				$this->__('Add all nine to Cart'),
				$this->__('Add all ten to Cart'),
			),
			'addToWishlistLabels' => array(
				$this->__('Add all to Wishlist'), 
				$this->__('Add to Wishlist'),
				$this->__('Add both to Wishlist'),
				$this->__('Add all three to Wishlist'),
				$this->__('Add all four to Wishlist'),
				$this->__('Add all five to Wishlist'),
				$this->__('Add all six to Wishlist'),
				$this->__('Add all seven to Wishlist'),
				$this->__('Add all eight to Wishlist'),
				$this->__('Add all nine to Wishlist'),
				$this->__('Add all ten to Wishlist'),
			),
			'addToCompareLabels' => array(
				$this->__('Add all to Compare'), 
				$this->__('Add to Compare'),
				$this->__('Add both to Compare'),
				$this->__('Add all three to Compare'),
				$this->__('Add all four to Compare'),
				$this->__('Add all five to Compare'),
				$this->__('Add all six to Compare'),
				$this->__('Add all seven to Compare'),
				$this->__('Add all eight to Compare'),
				$this->__('Add all nine to Compare'),
				$this->__('Add all ten to Compare'),
			),
			'numberFormat' => Mage::helper('mana_core')->getJsPriceFormat(),
			'productId' => $this->_getProduct()->getId(),
		));
        
    	return $this;
    }
}