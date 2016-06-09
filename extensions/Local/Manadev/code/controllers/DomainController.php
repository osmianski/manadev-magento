<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_DomainController extends Mage_Core_Controller_Front_Action
{
    /**
     * Check customer authentication
     */
    public function preDispatch()
    {
        parent::preDispatch();

        $loginUrl = Mage::helper('customer')->getLoginUrl();

        if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }
    }

    /**
     * @return Local_Manadev_Model_Downloadable_Item
     */
    protected function _getItemModelFromRequest() {
        $id = $this->getRequest()->getParam('id', 0);
        /** @var Local_Manadev_Model_Downloadable_Item $linkPurchasedItem */
        $linkPurchasedItem = Mage::getModel('downloadable/link_purchased_item')->load($id, 'link_hash');

        return $linkPurchasedItem;
    }

    protected function _init() {
        $linkPurchasedItem = $this->_getItemModelFromRequest();

        if(!$linkPurchasedItem->getId()) {
            $this->_forward('defaultNoRoute');
            return $this;
        }
        /** @var Mage_Downloadable_Model_Link_Purchased $linkPurchased */
        $linkPurchased = Mage::getModel('downloadable/link_purchased')->load($linkPurchasedItem->getPurchasedId());

        if ($this->_getCustomerSession()->getCustomerId() != $linkPurchased->getCustomerId()) {
            $this->_getSession()->addError(Mage::helper('local_manadev')->__("You do not have access to this downloadable item."));
            $this->_redirect('');
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

    public function linkAction() {
        $linkPurchasedItem = $this->_getItemModelFromRequest();
        if($linkPurchasedItem->getStatus() == Local_Manadev_Model_Download_Status::M_LINK_STATUS_NOT_REGISTERED) {
            return $this->_redirect('*/*/register', array('id' => $this->getRequest()->getParam('id')));
        } elseif(in_array($linkPurchasedItem->getStatus(), array(Local_Manadev_Model_Download_Status::M_LINK_STATUS_AVAILABLE, Local_Manadev_Model_Download_Status::M_LINK_STATUS_AVAILABLE_TIL,
            Local_Manadev_Model_Download_Status::M_LINK_STATUS_PERIOD_EXPIRED))) {
            return $this->_redirect('downloadable/download/link', array('id' => $this->getRequest()->getParam('id')));
        }

        return $this->_redirect('');
    }

    public function saveAction() {
        $linkPurchasedItem = $this->_getItemModelFromRequest();
        if (!$linkPurchasedItem->getId()) {
            $this->_redirect('');
            return $this;
        }
        /** @var Mage_Downloadable_Model_Link_Purchased $linkPurchased */
        $linkPurchased = Mage::getModel('downloadable/link_purchased')->load($linkPurchasedItem->getPurchasedId());

        if ($this->_getCustomerSession()->getCustomerId() != $linkPurchased->getCustomerId()) {
            $this->_getSession()->addError(Mage::helper('local_manadev')->__("You do not have access to this downloadable item."));
            $this->_redirect('');
            return $this;
        }


        try{
            $domain = $this->getRequest()->getParam('domain', false);
            if($domain) {
                $domain = $this->_validateDomain(trim($this->getRequest()->getParam('domain')));
            }
            $storeInfo = $this->getRequest()->getParam('m_store_info', "");
            if(trim($domain) === "" && trim($storeInfo) === "") {
                throw new Mana_Core_Exception_Validation(Mage::helper('local_manadev')->__("Please provide either your store admin panel URL or your store information."));
            }
        } catch(Mana_Core_Exception_Validation $e) {
            $this->_getSession()->addError($e->getErrors());
            $this->_getSession()->setData('post_data', $this->getRequest()->getParams());
            $this->_redirect('*/*/register', array('id' => $this->getRequest()->getParam('id')));

            return $this;
        }

        $this->_getHelper()->createNewZipFileWithLicense($linkPurchasedItem);

        $platform = Mage::getResourceModel('catalog/product')->getAttributeRawValue($linkPurchasedItem->getData('product_id'), 'platform', 0);

        // Magento 2 only uses 'available_til'
        $status = ($platform == Local_Manadev_Model_Platform::VALUE_MAGENTO_2) ?
            Local_Manadev_Model_Download_Status::M_LINK_STATUS_AVAILABLE_TIL :
            Local_Manadev_Model_Download_Status::M_LINK_STATUS_AVAILABLE;

        $linkPurchasedItem
            ->setData('m_registered_domain', $domain)
            ->setData('m_store_info', $storeInfo)
            ->setData('status', $status)
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
                ->addSuccess('Registered URL has been updated.');
        }

        if($this->_getCustomerSession()->getData('m_start_download', true)) {
            if ($installationInstructionUrl = $product->getData('installation_instruction_url')) {
                $this->_redirect('', array('_direct' => ltrim($installationInstructionUrl, '/')));
            } else {
                $this->_redirect('downloadable/customer/products');
            }
        } else {
            $this->_redirect('downloadable/customer/products');
        }
        return $this;
    }

    protected function getPage($url) {
        $useragent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.89 Safari/537.36';
        $timeout = 120;
        $dir = dirname(__FILE__);
        $cookie_file = $dir . '/cookies/' . md5($_SERVER['REMOTE_ADDR']) . '.txt';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        curl_setopt($ch, CURLOPT_REFERER, 'http://www.google.com/');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $content = curl_exec($ch);
        curl_close($ch);
        unlink($cookie_file);
        return $content;
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

    /**
     * @param $url
     * @return array
     */
    public function _getHeaders(&$url, $recursionLevel = 0) {
        $headers = @get_headers($url);
        $orig_url = $url;
        if (strpos($headers[0], '200') !== false) {
            return true;
        }

        if (strpos($headers[0], '302') !== false) {
            $newUrl = "";
            foreach ($headers as $header) {
                if (strpos($header, "Location: ") !== false) {
                    $newUrl = str_replace("Location: ", "", $header);
                    $url = $newUrl;
                    break;
                }
            }
            // If the domain is the same but only added `key` in parameter (Magento Secret Key), assume valid.
            if (strpos($newUrl, $orig_url) === 0 && strpos($newUrl, "/key/") !== false) {
                return true;
            } else {
                if($recursionLevel < 5) {
                    return $this->_getHeaders($url, $recursionLevel + 1);
                } else {
                    return false;
                }
            }
        }

        return false;
    }

    /**
     * @return string
     * @throws Mana_Core_Exception_Validation
     */
    public function _validateDomain($postDomain) {
        if (strpos($postDomain, "http") === false && trim($postDomain) != "") {
            $postDomain = "http://" . $postDomain;
        }
        $postDomain = trim($postDomain, "/") . "/";
        $url = $postDomain;

        try{
            if (filter_var($url, FILTER_VALIDATE_URL) === false) {
                throw new Exception;
            }

            $isResponseValid = $this->_getHeaders($url);

            if (!$isResponseValid) {
                throw new Exception;
            }

            $contents = $this->getPage($url);

            if (strpos($contents, "name=\"login[username]\"") === false || strpos($contents, "name=\"login[password]\"") === false) {
                // Retry with original url and append `/admin`
                $url = $postDomain . "admin/";
                $isResponseValid = $this->_getHeaders($url);

                if (!$isResponseValid) {
                    throw new Exception;
                }

                $contents = $this->getPage($url);
                if (strpos($contents, "name=\"login[username]\"") === false || strpos($contents, "name=\"login[password]\"") === false) {
                    throw new Exception;
                } else {
                    $postDomain .= "admin/";
                }
            }
        } catch(Exception $e) {
            throw new Mana_Core_Exception_Validation(sprintf(Mage::helper('local_manadev')->__("`%s` is not a Magento Admin Panel URL."), $postDomain));
        }

        $domain = $postDomain;

        return $domain;
    }
}