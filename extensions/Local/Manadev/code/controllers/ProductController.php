<?php
/**
 * @category    Mana
 * @package     Local_Manadev
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * This controller's actions are available using relative url actions/product/... 
 * @author Mana Team
 *
 */
class Local_Manadev_ProductController extends Mage_Core_Controller_Front_Action {
	/**
	 * Returns object containing current user's customer data
	 * @return Mage_Customer_Model_Session
	 */
	protected function _getCustomerSession() {
		return Mage::getSingleton('customer/session');
	}
	/**
	 * Return product object based on id specified in URL. Does various checks before letting person to download
	 * @param bool $silent Set this to false if you like exeception to be thrown in case product checks fail
	 * @return NULL|Mage_Catalog_Model_Product
	 */
	protected function _initProduct($silent = true) {
		try {
			// if we could not identify product, person will be redirected to hoe page.
			$product = null;
			
			// try identify product
			if (!($productId = $this->getRequest()->getParam('id'))) throw new Mage_Core_Exception($this->__('No product specified.'));
			if (Mage::helper('mana_core')->endsWith($productId, '.zip')) {
			    $productId = substr($productId, 0, strlen($productId) - strlen('.zip'));
			}
			/* @var $product Mage_Catalog_Model_Product */ $product = Mage::getModel(strtolower('Catalog/Product'));
			$product->load($productId);
			if (!$product->getId()) throw new Mage_Core_Exception($this->__('Product %d does not exist', $productId));
			
			Mage::register('product', $product); // thank you block will need this

			// decide if product is suitable to be downloaded
			if ($product->getTypeId() != 'downloadable') throw new Mage_Core_Exception($this->__('Product "%s" (%d) is not downloadable.', $product->getName(), $productId));
			if ($product->getPrice() > 0) throw new Mage_Core_Exception($this->__('Product "%s" (%d) is not free.', $product->getName(), $productId));
		}
		catch (Mage_Core_Exception $e) {
			return $this->_reportFailureAndRedirect($e->getMessage(), $productId, $product, $silent);
		}
		catch (Exception $e) {
			throw $e;
		} 
		return $product;
	}
	protected function _validateCustomerPermissions($product) {
		try {
			// decide if person has permission to download
			if (!$this->_getCustomerSession()->isLoggedIn()) { // registered customers are always allowed to download freebies
				// guest downloads are generally ok, but only through our user interface, no robots please
				if (($permissions = $this->_getCustomerSession()->getMDownloadPermissions()) === null || 
					!isset($permissions[$product->getId()]) || !$permissions[$product->getId()])
				{
					throw new Mage_Core_Exception($this->__('Guest does not have permissions to download product "%s" (%d) is not free.', $product->getName(), $product->getId()));
				}
			}
		}
		catch (Mage_Core_Exception $e) {
			return $this->_reportFailureAndRedirect($e->getMessage(), $product->getId(), $product);
		}
		catch (Exception $e) {
			throw $e;
		} 
		return true;
	} 
	/**
	 * Logs download failure and redirects person away from download page
	 * @param string $message
	 * @param int $productId
	 * @param Mage_Catalog_Model_Product $product
	 * @param bool $silent Set this to false if you like exeception to be thrown in case product checks fail
	 * @return NULL
	 */
	protected function _reportFailureAndRedirect($message, $productId, $product, $silent = true) {
		// log illegal attempt for monitoring
		/* @var $failure Local_Manadev_Model_Download_Failure */ $failure = Mage::getModel(strtolower('Local_Manadev/Download_Failure'));
		$failure->setMessage($message)->setProductId($productId)
			->setCreatedAt(date(DATE_ATOM))->setIpAddress($this->getRequest()->getClientIp())
			->setIsGuest($this->_getCustomerSession()->isLoggedIn())
			->setCustomerId($this->_getCustomerSession()->getCustomerId())
			->save();
			
		if ($silent) {
			// add error message
			$this->_getCustomerSession()->addError($this->__('Problem occured during preparing product for more details. Sorry for inconvenience.'));
			
			// redirect to product page
			if ($product && $product->getId()) {
				$this->_redirect('catalog/product/view', array('id' => $productId, '_use_rewrite' => true));
			}
			else {
				$this->_redirect('');
			}
			return null;
		}
		else {
			throw new Exception($this->__('Problem occured during preparing product for more details. Sorry for inconvenience.'));
		}
	}
	/**
	 * Full page rendering. "Thank you for downloading" page with automatic download initiation.
	 */
	public function downloadAction() {
		if (!($product = $this->_initProduct()) || !$this->_validateCustomerPermissions($product)) return;
		
		// update download stats for this product
		/* @var $stats Local_Manadev_Model_Download */ $stats = Mage::getModel(strtolower('Local_Manadev/Download'));
		$stats->setProductId($product->getId())
			->setCreatedAt(date(DATE_ATOM))->setIpAddress($this->getRequest()->getClientIp())
			->setIsGuest($this->_getCustomerSession()->isLoggedIn())
			->setCustomerId($this->_getCustomerSession()->getCustomerId())
			->save();

		if ($installationInstructionUrl = $product->getData('installation_instruction_url')) {
			$this->_getCustomerSession()
				->addSuccess('Thank you for you interest in our products. Please find detailed installation instructions below.')
				->setData('pending_download_product_id', $product->getId());
			$this->_redirect('', array('_direct' => ltrim($installationInstructionUrl, '/')));
			return;
		}

		// render thank you page
		$pageId = Mage::getStoreConfig('local_manadev/downloads/thank_you_page');
		if (!$pageId) $pageId = Mage::getStoreConfig('web/default/cms_home_page');
		/* @var $cms Mage_Cms_Helper_Page */ $cms = Mage::helper(strtolower('Cms/Page'));
		$cms->renderPage($this, $pageId);
	}
	/**
	 * File stream action. Streams downloadable product to browser
	 */
	public function fileAction() {
		if (!($product = $this->_initProduct()) || !$this->_validateCustomerPermissions($product)) return;
				
		// retrieve full file name of a downloadable 
		/* @var $links Mage_Downloadable_Model_Mysql4_Link_Collection */ $links = Mage::getResourceModel(strtolower('Downloadable/Link_Collection'));
		$links->addFieldToFilter('product_id', $product->getId())->setPageSize(1)->setCurPage(1);
		/* @var $link Mage_Downloadable_Model_Link */ $link = null; foreach ($links as $link) break; // take first element
		if (!$link) return $this->_reportFailureAndRedirect($this->__('Product %s (%d) and no downloadable files attached.', $product->getName(), $product->getId()), $product->getId(), $product);

		$platform = Mage::getResourceModel('catalog/product')->getAttributeRawValue($product->getId(), 'platform', 0);
		$status = ($platform == Local_Manadev_Model_Platform::VALUE_MAGENTO_2) ?
			Local_Manadev_Model_Download_Status::M_LINK_STATUS_AVAILABLE_TIL :
			Local_Manadev_Model_Download_Status::M_LINK_STATUS_AVAILABLE;
		/** @var Local_Manadev_Model_Downloadable_Item $linkPurchasedItem */
		$linkPurchasedItem = Mage::getModel('downloadable/link_purchased_item');
		$linkHash = strtr(base64_encode(microtime() . $product->getId()), '+/=', '-_,');
		$linkPurchasedItem
			->setOrderItemId(null)
			->setPurchasedId(null)
			->setLinkId($link->getId())
			->setProductId($product->getId())
			->setIsShareable(0)
			->setLinkFile($link->getLinkFile())
			->setLinkType($link->getLinkType())
			->setStatus($status)
			->setLinkHash($linkHash)
			->setCreatedAt(strftime('%Y-%m-%d', time()))
			->setUpdatedAt(strftime('%Y-%m-%d', time()))
			->setData('m_is_free', 1)
			->save();
		/** @var Local_Manadev_Helper_Data $helper */
		$helper = Mage::helper('local_manadev');
		if($helper->createNewZipFileWithLicense($linkPurchasedItem)) {
			$linkPurchasedItem->save();
		}


		/* @var $storage Mage_Downloadable_Helper_File */ $storage = Mage::helper('downloadable/file');
		$resource  = $storage->getFilePath(Mage_Downloadable_Model_Link::getBasePath(), $linkPurchasedItem->getLinkFile());
		
		// prepare headers - in other words, tell browser that this will be not page but file, tell its size, type,
		// tell not to cache results
		/* @var $downloader Mage_Downloadable_Helper_Download */ $downloader = Mage::helper('downloadable/download');
		$downloader->setResource($resource, Mage_Downloadable_Helper_Download::LINK_TYPE_FILE);
		$fileName       = $downloader->getFilename();
        $contentType    = $downloader->getContentType();
        $this->getResponse()
            ->setHttpResponseCode(200)->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-type', $contentType, true);

        if ($fileSize = $downloader->getFilesize()) {
            $this->getResponse()->setHeader('Content-Length', $fileSize);
        }
        if ($contentDisposition = $downloader->getContentDisposition()) {
            $this->getResponse()->setHeader('Content-Disposition', $contentDisposition . '; filename='.$fileName, true);
        }
        
        // send headers and raw file bytes
        $this->getResponse()->clearBody();
        $this->getResponse()->sendHeaders();
        $downloader->output();
	}
	/**
	 * AJAX remote call. Marks flag in session to allow download of a specific product from this session.
	 */
	public function allowGuestDownloadAction() {
		try {
			$product = $this->_initProduct(false);
			if (!$this->_getCustomerSession()->isLoggedIn()) { // registered customers are always allowed to download freebies
				// guest downloads are generally ok, but only through our user interface, no robots please
				if (($permissions = $this->_getCustomerSession()->getMDownloadPermissions()) === null) {
					$permissions = array();
				}
				$permissions[$product->getId()] = true;
				$this->_getCustomerSession()->setMDownloadPermissions($permissions);
			}
			else {
				throw new Exception($this->__('Customer is not expected to be logged in.'));
			}
        }
	    catch (Exception $e) {
			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('error' => $e->getMessage())));
	    }
	}
}