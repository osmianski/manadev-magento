<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_DomainController extends Mage_Core_Controller_Front_Action
{
    protected function _init() {
        $id = $this->getRequest()->getParam('id', 0);
        /** @var Mage_Downloadable_Model_Mysql4_Link_Purchased_Item $linkPurchasedItem */
        $linkPurchasedItem = Mage::getModel('downloadable/link_purchased_item')->load($id, 'link_hash');

        if(!$linkPurchasedItem->getId()) {
            $this->_forward('defaultNoRoute');
            return $this;
        }

        if($post_data = $this->_getSession()->getData('post_data')) {
            $domain = reset($post_data['domain']);
            $linkPurchasedItem
                ->setData('m_registered_domain', $domain)
                ->setData('m_store_info', $post_data['m_store_info']);
            $this->_getSession()->unsetData('post_data');
        }

        Mage::register('m_purchased_item', $linkPurchasedItem);
        $this->loadLayout();
        $this->renderLayout();
        return $this;
    }

    public function registerAction() {
        $this->_getCustomerSession()->setData('m_start_download', true);
        return $this->_init();
    }

    public function modifyAction() {
        $this->_getCustomerSession()->setData('m_start_download', false);
        return $this->_init();
    }

    public function saveAction() {
        $id = $this->getRequest()->getParam('id', 0);
        /** @var Mage_Downloadable_Model_Mysql4_Link_Purchased_Item $linkPurchasedItem */
        $linkPurchasedItem = Mage::getModel('downloadable/link_purchased_item')->load($id, 'link_hash');
        if (!$linkPurchasedItem->getId()) {
            $this->_redirect('');
            return $this;
        }

        try{
            $urls = $this->getRequest()->getParam('domain');
            foreach($urls as $x => $url) {
                if(trim($url) === "") {
                    unset($urls[$x]);
                    continue;
                }

                if (filter_var($url, FILTER_VALIDATE_URL) === false) {
                    throw new Mana_Core_Exception_Validation(sprintf(Mage::helper('local_manadev')->__("Invalid URL: %s"), $url));
                }

                $headers = @get_headers($url);
                // Also allow 302 because of magento secret key redirect
                if(!(strpos($headers[0],'200') === false || strpos($headers[0], '302') === false)) {
                    throw new Mana_Core_Exception_Validation(sprintf(Mage::helper('local_manadev')->__("URL `%s` did not return a 200 OK response."), $url));
                }
            }
            $domain = implode(",", $urls);
            $storeInfo = $this->getRequest()->getParam('m_store_info', "");
            if(trim($domain) === "" && trim($storeInfo) === "") {
                throw new Mana_Core_Exception_Validation(Mage::helper('local_manadev')->__("Please provide either your store admin panel URL or your store information."));
            }
        } catch(Mana_Core_Exception_Validation $e) {
            $this->_getSession()->addError($e->getErrors());
            $this->_getSession()->setData('post_data', $this->getRequest()->getParams());
            $this->_redirect('*/*/register', array('id' => $id));

            return $this;
        }

        $this->_getHelper()->createNewZipFileWithLicense($linkPurchasedItem);

        $linkPurchasedItem
            ->setData('m_registered_domain', $domain)
            ->setData('m_store_info', $storeInfo)
            ->setData('status', Local_Manadev_Model_Download_Status::M_LINK_STATUS_AVAILABLE)
            ->save();

        /** @var Local_Manadev_Resource_DomainHistory $dhResource */
        $dhResource = Mage::getResourceModel('local_manadev/domainHistory');
        $dhResource->insertHistory($linkPurchasedItem->getId(), $domain, $storeInfo);


        /* @var $product Mage_Catalog_Model_Product */ $product = Mage::getModel(strtolower('catalog/product'));
        $productId = $linkPurchasedItem->getData('product_id');
        $product->load($productId);

        if (!$product->getId()) throw new Mage_Core_Exception($this->__('Product %d does not exist', $productId));

        if($this->_getCustomerSession()->getData('m_start_download')) {
            $this->_getCustomerSession()
                ->addSuccess('Thank you for registering your domain. Your product download shall start automatically.')
                ->setData('m_pending_download_link_hash', $linkPurchasedItem->getLinkHash());
        } else {
            $this->_getCustomerSession()
                ->addSuccess('Thank you for updating your domain.');
        }

        $this->_getCustomerSession()->unsetData('m_start_download');

        if ($installationInstructionUrl = $product->getData('installation_instruction_url')) {
            $this->_redirect('', array('_direct' => ltrim($installationInstructionUrl, '/')));
        } else {
            $this->_redirect('downloadable/customer/products');
        }

        return $this;
    }

    /**
     * Returns object containing current user's customer data
     * @return Mage_Customer_Model_Session
     */
    protected function _getCustomerSession() {
        return Mage::getSingleton('customer/session');
    }

    /**
     * @return Mage_Core_Model_Abstract
     */
    protected function _getSession() {
        return Mage::getSingleton('core/session');
    }

    /**
     * @return Local_Manadev_Helper_Data
     */
    protected function _getHelper() {
        return Mage::helper('local_manadev');
    }
}