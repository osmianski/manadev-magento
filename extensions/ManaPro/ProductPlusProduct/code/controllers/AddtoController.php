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
class ManaPro_ProductPlusProduct_AddtoController  extends Mage_Core_Controller_Front_Action {
    /**
     * Retrieve shopping cart model object
     *
     * @return Mage_Checkout_Model_Cart
     */
    protected function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }

    /**
     * Get checkout session model instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get current active quote instance
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote()
    {
        return $this->_getCart()->getQuote();
    }
	
    public function cartAction() {
    	/* @var $core Mana_Core_Helper_Data */ $core = Mage::helper(strtolower('Mana_Core'));
        $cart   = $this->_getCart();
        $params = $this->getRequest()->getParams();
        $productIds = $params['product'];
        unset($params['product']);
        if (isset($params['product_info'])) {
        	$productInfo = array();
        	if ($core->startsWith($params['product_info'], '"') && $core->endsWith($params['product_info'], '"')) {
        		$params['product_info'] = substr($params['product_info'], 1, strlen($params['product_info']) - 2);
        	}
        	parse_str(urldecode($params['product_info']), $productInfo);
        	if (isset($productInfo['qty'])) {
        		unset($productInfo['qty']);
        	}
        	unset($params['product_info']);
        }
        else {
        	$productInfo = null;
        }
        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }
			
            $products = array();
            foreach ($productIds as $productId) {
		        if ($productId) {
		            $product = Mage::getModel('catalog/product')
		                ->setStoreId(Mage::app()->getStore()->getId())
		                ->load($productId);
		            if (!$product->getId()) {
		                continue;
		            }
		            $products[] = $product;
		            if ($productInfo && $productInfo['product'] == $productId) {
		            	$cart->addProduct($product, array_merge($productInfo, $params));
		            }
		            else {
		            	$cart->addProduct($product, $params);
		            }
		        }
            }

            /**
             * Check product availability
             */
            if (!count($products)) {
                $this->_goBack();
                return;
            }

            $cart->save();
            $this->_getSession()->setCartWasUpdated(true);

            foreach ($products as $product) {
	            Mage::dispatchEvent('checkout_cart_add_product_complete',
	                array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
	            );
            }

            if (!$this->_getSession()->getNoCartRedirect(true)) {
                if (!$cart->getQuote()->getHasError()){
                	foreach ($products as $product) {
	                    $message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->htmlEscape($product->getName()));
	                    $this->_getSession()->addSuccess($message);
                	}
                }
                $this->_goBack();
            }
        }
        catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $this->_getSession()->addNotice($e->getMessage());
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_getSession()->addError($message);
                }
            }

            $url = $this->_getSession()->getRedirectUrl(true);
            if ($url) {
                $this->getResponse()->setRedirect($url);
            } else {
                $this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
            }
        }
        catch (Exception $e) {
        	Mage::logException($e);
        	if (isset($product)) {
            	$this->_getSession()->addException($e, $this->__('Cannot add %s to shopping cart.', $product->getName()));
        	}
        	else {
            	$this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
        	}
            $this->_goBack();
        }
	}
	public function wishlistAction() {
        $session = Mage::getSingleton('customer/session');
        $wishlist = $this->_getWishlist();
        if (!$wishlist) {
            $this->_redirect('*/');
            return;
        }

        $params = $this->getRequest()->getParams();
        $productIds = $params['product'];
        unset($params['product']);
        
        try {
	        foreach ($productIds as $productId) {
		        $product = Mage::getModel('catalog/product')->load($productId);
		        if (!$product->getId() || !$product->isVisibleInCatalog()) {
		            continue;
		        }
	            $wishlist->addNewItem($product->getId(), new Varien_Object());
	            $wishlist->save();
	
	            Mage::dispatchEvent('wishlist_add_product', array('wishlist'=>$wishlist, 'product'=>$product));
	
	            if ($referer = $session->getBeforeWishlistUrl()) {
	                $session->setBeforeWishlistUrl(null);
	            }
	            else {
	                $referer = $this->_getRefererUrl();
	            }
	
	            /**
	             *  Set referer to avoid referring to the compare popup window
	             */
	            $session->setAddActionReferer($referer);
	
	            Mage::helper('wishlist')->calculate();
	
	            $message = $this->__('%1$s has been added to your wishlist. Click <a href="%2$s">here</a> to continue shopping', $product->getName(), $referer);
	            $session->addSuccess($message);
	        }
        }
        catch (Mage_Core_Exception $e) {
            $session->addError($this->__('An error occurred while adding item to wishlist: %s', $e->getMessage()));
        }
        catch (Exception $e) {
            $session->addError($this->__('An error occurred while adding item to wishlist.'));
        }
        $this->_redirect('wishlist/');
	}
	public function compareAction() {
        $params = $this->getRequest()->getParams();
        $productIds = $params['product'];
        unset($params['product']);
		foreach ($productIds as $productId) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productId);

            if ($product->getId()/* && !$product->isSuper()*/) {
                Mage::getSingleton('catalog/product_compare_list')->addProduct($product);
                Mage::getSingleton('catalog/session')->addSuccess(
                    $this->__('The product %s has been added to comparison list.', Mage::helper('core')->htmlEscape($product->getName()))
                );
                Mage::dispatchEvent('catalog_product_compare_add_product', array('product'=>$product));
            }

            Mage::helper('catalog/product_compare')->calculate();
        }

        $this->_redirectReferer();
	}
    protected function _goBack()
    {
        if ($returnUrl = $this->getRequest()->getParam('return_url')) {
            // clear layout messages in case of external url redirect
            if ($this->_isUrlInternal($returnUrl)) {
                $this->_getSession()->getMessages(true);
            }
            $this->getResponse()->setRedirect($returnUrl);
        } elseif (!Mage::getStoreConfig('checkout/cart/redirect_to_cart')
            && !$this->getRequest()->getParam('in_cart')
            && $backUrl = $this->_getRefererUrl()) {

            $this->getResponse()->setRedirect($backUrl);
        } else {
            if (($this->getRequest()->getActionName() == 'add') && !$this->getRequest()->getParam('in_cart')) {
                $this->_getSession()->setContinueShoppingUrl($this->_getRefererUrl());
            }
            $this->_redirect('checkout/cart');
        }
        return $this;
    }
    /**
     * Retrieve wishlist object
     *
     * @return Mage_Wishlist_Model_Wishlist|false
     */
    protected function _getWishlist()
    {
        try {
            $wishlist = Mage::getModel('wishlist/wishlist')
                ->loadByCustomer(Mage::getSingleton('customer/session')->getCustomer(), true);
            Mage::register('wishlist', $wishlist);
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('wishlist/session')->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::getSingleton('wishlist/session')->addException($e,
                Mage::helper('wishlist')->__('Cannot create wishlist.')
            );
            return false;
        }
        return $wishlist;
    }
    public function preDispatch()
    {
        parent::preDispatch();

        if ($this->getRequest()->getActionName() == 'wishlist') {
	        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
	            $this->setFlag('', 'no-dispatch', true);
	            if(!Mage::getSingleton('customer/session')->getBeforeWishlistUrl()) {
	                Mage::getSingleton('customer/session')->setBeforeWishlistUrl($this->_getRefererUrl());
	            }
	            Mage::getSingleton('customer/session')->setMProduct($this->getRequest()->getParam('product'));
	        }
	        else {
	        	if (!$this->getRequest()->getParam('product')) {
	        		if ($products = Mage::getSingleton('customer/session')->getMProduct()) {
	        			$this->getRequest()->setParam('product', $products);
	        			Mage::getSingleton('customer/session')->unsMProduct();
	        		}
	        	}
	        }
	        if (!Mage::getStoreConfigFlag('wishlist/general/active')) {
	            $this->norouteAction();
	            return;
	        }
        }
    }

}