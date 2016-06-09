<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_SupportController extends Mage_Core_Controller_Front_Action
{
    const XML_PATH_EMAIL_RECIPIENT = 'local_manadev_emails/open_support_ticket/to';
    const XML_PATH_EMAIL_SENDER = 'local_manadev_emails/open_support_ticket/identity';
    const XML_PATH_EMAIL_TEMPLATE = 'local_manadev_emails/open_support_ticket/template';
    const XML_PATH_ENABLED = 'local_manadev_emails/open_support_ticket/enabled';

    public function preDispatch() {
        parent::preDispatch();

        if (!Mage::getStoreConfigFlag(self::XML_PATH_ENABLED)) {
            $this->norouteAction();
        }
    }


    protected function _init() {
        $linkPurchasedItem = $this->_registerItem();

        /** @var Mage_Downloadable_Model_Link_Purchased $linkPurchased */
        $linkPurchased = Mage::getModel('downloadable/link_purchased')->load($linkPurchasedItem->getPurchasedId());

        if(!$linkPurchasedItem->getId() || $linkPurchased->getCustomerId() != Mage::getSingleton('customer/session')->getId()) {
            $this->_forward('defaultNoRoute');
            return $this;
        }

        $this->loadLayout();
        $this->renderLayout();
        return $this;
    }

    public function extendAction() {
        $linkPurchasedItem = $this->_registerItem();
        $product = Mage::getModel('catalog/product')->loadByAttribute('sku', Mage::getStoreConfig('local_manadev/support_services_sku'));

        $url = Mage::helper('checkout/cart')->getAddUrl($product, array('m_license' => $linkPurchasedItem->getId()));

        return $this->_redirectUrl($url);
    }

    public function openTicketAction(){
        $this->_init();
        $this->_getSession()->unsetData('post_data');
    }

    public function submitAction() {
        if(!Mage::getSingleton('customer/session')->getId()) {
            return $this->_redirect('');
        }
        $linkPurchasedItem = $this->_registerItem();
        /** @var Mage_Downloadable_Model_Link_Purchased $linkPurchased */
        $linkPurchased = Mage::getModel('downloadable/link_purchased')->load($linkPurchasedItem->getPurchasedId());

        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->load($linkPurchased->getOrderId());

        $issueDetails = $this->getRequest()->getParam('issue_details');
        $vars = array(
            'order' => $order,
            'purchased_item' => $linkPurchasedItem,
            'issue_details' => $issueDetails,
        );

        try {
            if(!$issueDetails) {
                throw new Exception("Issue details cannot be blank.");
            }

            $files = array();
            try{
                for($x = 1; $x <= 3; $x++) {
                    if($_FILES['screenshot_'.$x]['name'] != "" && $_FILES['screenshot_'.$x]['error'] == 1) {
                        throw new Mana_Core_Exception_Validation("");
                    }
                }
                for($x = 1; $x <= 3; $x++) {
                    if (isset($_FILES['screenshot_'.$x]['name']) && $_FILES['screenshot_' . $x]['name'] != '') {
                        $fileName = $_FILES['screenshot_' . $x]['name'];
                        $fileExt = strtolower(substr(strrchr($fileName, ".") ,1));
                        $fileNamewoe = rtrim($fileName, $fileExt);
                        $fileName = preg_replace('/\s+', '', $fileNamewoe) . time() . '.' . $fileExt;

                        $uploader = new Varien_File_Uploader('screenshot_' . $x);
                        $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'png', 'gif'));
                        $uploader->setAllowRenameFiles(true);
                        $uploader->setFilesDispersion(false);
                        $path = Mage::getBaseDir('media') . DS . 'support-ticket-images';
                        if(!is_dir($path)){
                            mkdir($path, 0777, true);
                        }
                        if(!$uploader->checkAllowedExtension($uploader->getFileExtension())) {
                            throw new Mana_Core_Exception_Validation("");
                        }

                        $result = $uploader->save($path . DS, $fileName );
                        $files[] = $result['path'] . $result['file'];
                    }
                }
            }
            catch(Mana_Core_Exception_Validation $e) {
                $sizeLimit = ini_get("upload_max_filesize");
                throw new Exception(sprintf(Mage::helper('local_manadev')->__("Can't upload screenshot files. Screenshot files must be JPEG, JPG, GIF, and PNG files that is not larger than %s each."), $sizeLimit));
            }
            catch(Exception $e) {
                throw new Exception("Something went wrong with the attached files. Please try again.");
            }
        } catch(Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_getSession()->setData('post_data', $this->getRequest()->getParams());
            return $this->_redirect('*/*/openTicket', array('id' => $this->getRequest()->getParam('id')));
        }

        try {
            $mailTemplate = Mage::getModel('core/email_template');
            /* @var $mailTemplate Mage_Core_Model_Email_Template */
            $mailTemplate->setDesignConfig(array('area' => 'frontend'))
                ->setReplyTo($order->getCustomerEmail());

            foreach($files as $attachFile) {
                $fileContents = file_get_contents($attachFile);
                $attachment = $mailTemplate->getMail()->createAttachment($fileContents);
                $attachment->filename = array_pop(explode(DS, $attachFile));
            }

            $mailTemplate
                ->sendTransactional(
                    Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE),
                    Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER),
                    explode(",", Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT)),
                    null,
                    $vars
                );

            if (!$mailTemplate->getSentSuccess()) {
                throw new Exception();
            }

            Mage::getSingleton('customer/session')->addSuccess(
                Mage::helper('local_manadev')->__('Your support ticket was submitted. Our Technical Support Representative will be in touch with you as soon as possible.')
            );
        } catch (Exception $error) {
            Mage::getSingleton('customer/session')->addError(
                Mage::helper('local_manadev')->__('Unable to submit support ticket. Please try again later.')
            );
        }
        return $this->_redirect('downloadable/customer/products');
    }

    protected function _parseSize($size) {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
        $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
        if ($unit) {
            // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        } else {
            return round($size);
        }
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
     * @return Local_Manadev_Model_Downloadable_Item
     */
    protected function _registerItem() {
        $id = $this->getRequest()->getParam('id', 0);
        /** @var Local_Manadev_Model_Downloadable_Item $linkPurchasedItem */
        $linkPurchasedItem = Mage::getModel('downloadable/link_purchased_item')->load($id, 'link_hash');
        Mage::register('m_purchased_item', $linkPurchasedItem);

        return $linkPurchasedItem;
    }
}