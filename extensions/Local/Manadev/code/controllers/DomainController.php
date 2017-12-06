<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_DomainController extends Mage_Core_Controller_Front_Action
{
    protected $domainLoggingEnabled = true;

    const XML_PATH_EMAIL_RECIPIENT = 'local_manadev_emails/domain_confirmation/to';
    const XML_PATH_EMAIL_SENDER = 'local_manadev_emails/domain_confirmation/identity';
    const XML_PATH_EMAIL_TEMPLATE = 'local_manadev_emails/domain_confirmation/template';
    const XML_PATH_ENABLED = 'local_manadev_emails/domain_confirmation/enabled';
    const XML_PATH_BLACKLIST = 'local_manadev/downloads/blacklist';

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

    protected function _init($breadcrumb) {
        $linkPurchasedItem = $this->_getItemModelFromRequest();

        if(!$linkPurchasedItem->getId()) {
            $this->_forward('defaultNoRoute');
            return $this;
        }
        /** @var Mage_Downloadable_Model_Link_Purchased $linkPurchased */
        $linkPurchased = Mage::getModel('downloadable/link_purchased')->load($linkPurchasedItem->getPurchasedId());

        if ($this->_getCustomerSession()->getCustomerId() != $linkPurchased->getCustomerId()) {
            $this->_getSession()->addError($this->localHelper()->__("You do not have access to this downloadable item."));
            $this->_redirect('');
            return $this;
        }

        if($post_data = $this->_getSession()->getData('post_data')) {
            $domain = $post_data['domain'];
            $linkPurchasedItem
                ->setData('m_registered_domain', $domain)
                ->setData('m_store_info', $post_data['m_store_info']);
            $this->_getSession()->unsetData('post_data');
        }

        Mage::register('m_purchased_item', $linkPurchasedItem);
        $this->loadLayout();

        if (Mage::getStoreConfig('web/default/show_cms_breadcrumbs')
            && ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs'))
        ) {
            $breadcrumbs->addCrumb('my_licenses', array(
                'label' => Mage::helper('cms')->__('My Licenses and Downloads'),
                'title' => Mage::helper('cms')->__('My Licenses and Downloads'),
                'link' => Mage::getUrl('downloadable/customer/products')
            ));
            $breadcrumbs->addCrumb('presale', array(
                'label' => Mage::helper('cms')->__($breadcrumb),
                'title' => Mage::helper('cms')->__($breadcrumb)
            ));
        }

        $this->renderLayout();
        return $this;
    }

    public function registerAction() {
        $this->_getCustomerSession()->setData('m_start_download', true);
        return $this->_init('Register and Download');
    }

    public function modifyAction() {
        $this->_getCustomerSession()->setData('m_start_download', false);
        return $this->_init('Modify URL');
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
            $this->_getSession()->addError($this->localHelper()->__("You do not have access to this downloadable item."));
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
                throw new Mana_Core_Exception_Validation($this->localHelper()->__("Please provide either your store admin panel URL or your store information."));
            }
        } catch(Mana_Core_Exception_Validation $e) {
            $this->_getSession()->addError($e->getErrors());
            $this->_getSession()->setData('post_data', $this->getRequest()->getParams());

            $this->_redirectUrl($this->_getRefererUrl());
            return $this;
        }

        $this->_getHelper()->createNewZipFileWithLicense($linkPurchasedItem);

        $platform = Mage::getResourceModel('catalog/product')->getAttributeRawValue($linkPurchasedItem->getData('product_id'), 'platform', 0);

        // Magento 2 only uses 'available_til'
        $status = ($platform == Local_Manadev_Model_Platform::VALUE_MAGENTO_2) ?
            Local_Manadev_Model_Download_Status::M_LINK_STATUS_AVAILABLE_TIL :
            Local_Manadev_Model_Download_Status::M_LINK_STATUS_AVAILABLE;

        $linkPurchasedItem
            ->setData('m_registered_domain_pending', $domain)
            ->setData('m_store_info_pending', $storeInfo);
        $is_newly_registered = $this->_getCustomerSession()->getData('m_start_download');
        if($is_newly_registered) {
            // If store info set for the first time, no need for confirmation.
            $linkPurchasedItem->updateStoreInfoFromPending(false);
        }

        if ($linkPurchasedItem->getData('order_item_id')) {
            $orderItem = Mage::getModel('sales/order_item');
            $orderItem->load($linkPurchasedItem->getData('order_item_id'));
            $order = Mage::getModel('sales/order');
            $order->load($orderItem->getData('order_id'));

            foreach (array_filter(array_map('trim', explode("\n", Mage::getStoreConfig(self::XML_PATH_BLACKLIST)))) as $pattern) {

                if (preg_match($pattern, $order->getData('customer_email'))) {
                    $status = Local_Manadev_Model_Download_Status::M_LINK_STATUS_NOT_AVAILABLE;
                    $this->_getCustomerSession()->setData('m_start_download', false);
                    break;
                }
            }
        }

        $linkPurchasedItem
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
            // If it is disabled, then just update store info automatically
            $domain_registration_confirm_enabled = Mage::getStoreConfig(self::XML_PATH_ENABLED);
            if($domain_registration_confirm_enabled) {
                if($this->_sendConfirmationEmailToManaTeam($linkPurchasedItem)) {
                    $this->_getCustomerSession()
                        ->addSuccess('Registered URL has been submitted for review. It will be updated once it has been reviewed by the MANAdev Team.');
                }
            } else {
                $linkPurchasedItem->updateStoreInfoFromPending();
                $this->_getCustomerSession()
                    ->addSuccess('Registered URL has been updated.');
            }
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
        $timeout = 30;
        $cookie_file = BP . '/var/cookies/' . md5($_SERVER['REMOTE_ADDR']) . '.txt';

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
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $content = curl_exec($ch);
        if (!$content) {
            if ($this->domainLoggingEnabled) {
                Mage::log("No content for $url", Zend_Log::DEBUG, 'domain_validation.log');
                Mage::log("Curl error: " . curl_error($ch), Zend_Log::DEBUG, 'domain_validation.log');
            }
        }
        curl_close($ch);
        if (file_exists($cookie_file)) {
            unlink($cookie_file);
        }
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
        return $this->localHelper();
    }

    /**
     * @param $url
     * @param int $recursionLevel
     * @return bool
     */
    public function _getHeaders(&$url, $recursionLevel = 0) {
        if (($headers = @get_headers($url)) === false) {
            if ($this->domainLoggingEnabled) {
                Mage::log("$recursionLevel: get_headers($url) error", Zend_Log::DEBUG, 'domain_validation.log');
            }
            return false;
        }

        $orig_url = $url;
        if (strpos($headers[0], '200') !== false) {
            return true;
        }

        if (strpos($headers[0], '302') !== false || strpos($headers[0], '301') !== false) {
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
                    if ($this->domainLoggingEnabled) {
                        Mage::log("Redirecting $orig_url to $url", Zend_Log::DEBUG, 'domain_validation.log');
                    }

                    return $this->_getHeaders($url, $recursionLevel + 1);
                } else {
                    if ($this->domainLoggingEnabled) {
                        Mage::log("Too many redirects", Zend_Log::DEBUG, 'domain_validation.log');
                    }
                    return false;
                }
            }
        }

        if ($this->domainLoggingEnabled) {
            Mage::log("Unknown response for $orig_url: " . json_encode($headers), Zend_Log::DEBUG, 'domain_validation.log');
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
                if ($this->domainLoggingEnabled) {
                    Mage::log("Login form not found", Zend_Log::DEBUG, 'domain_validation.log');
                    //Mage::log($contents, Zend_Log::DEBUG, 'domain_validation.log');
                }
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
            if ($this->domainLoggingEnabled) {
                Mage::log("Invalid domain: $postDomain", Zend_Log::DEBUG, 'domain_validation.log');
                Mage::log("Domain tested: $url", Zend_Log::DEBUG, 'domain_validation.log');
                Mage::log("Error: {$e->getMessage()}", Zend_Log::DEBUG, 'domain_validation.log');
                Mage::log($e->getTraceAsString(), Zend_Log::DEBUG, 'domain_validation.log');
            }
            throw new Mana_Core_Exception_Validation(sprintf($this->localHelper()->__("`%s` is not a Magento Admin Panel URL."), $postDomain));
        }

        $domain = $postDomain;

        return $domain;
    }

    /**
     * @param Local_Manadev_Model_Downloadable_Item $linkPurchasedItem
     *
     * @return bool
     */
    protected function _sendConfirmationEmailToManaTeam($linkPurchasedItem) {
        try {
            $mailTemplate = Mage::getModel('core/email_template');
            $contactEmail = $this->_getCustomerSession()->getCustomer()->getEmail();
            /* @var $mailTemplate Mage_Core_Model_Email_Template */
            $mailTemplate->setDesignConfig(array('area' => 'frontend'))
                ->setReplyTo($contactEmail);

            $recipients = explode(",", Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT));
            $recipients = array_filter($recipients);
            if (!$recipients) {
                throw new Local_Manadev_Exception_NoRecipientException;
            }
            $pending_hash = $linkPurchasedItem->generatePendingHash();
            $confirmUrl = Mage::getUrl('*/domainChange/confirm', array('hash' => $pending_hash));

            $orderItem = Mage::getModel('sales/order_item')->load($linkPurchasedItem->getOrderItemId());
            $order = Mage::getModel('sales/order')->load($orderItem->getOrderId());

            $vars = array(
                'order' => $order,
                'purchased_item' => $linkPurchasedItem,
                'contact_email' => $contactEmail,
                'confirm_url' => $confirmUrl,
            );

            $mailTemplate
                ->sendTransactional(
                    Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE),
                    Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER),
                    $recipients,
                    null,
                    $vars
                );

            if (!$mailTemplate->getSentSuccess()) {
                throw new Exception();
            }

            return true;
        } catch (Local_Manadev_Exception_NoRecipientException $e) {
            $this->_getCustomerSession()->addError(
                $this->localHelper()->__('No recipient configured on System Configuration -> manadev.com Emails -> Domain Confirmation -> Send Email To')
            );
        } catch (Exception $e) {
            $this->_getCustomerSession()->addError(
                $this->localHelper()->__('Domain confirmation email sending failed. Please check access to the configured mail server in System Configuration -> System -> Mail Sending Settings.')
            );
        }

        return false;
    }

    /**
     * @return Local_Manadev_Helper_Data
     */
    protected function localHelper() {
        return Mage::helper('local_manadev');
    }
}