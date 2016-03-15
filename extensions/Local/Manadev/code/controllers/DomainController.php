<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_DomainController extends Mage_Core_Controller_Front_Action
{
    public function registerAction() {
        $id = $this->getRequest()->getParam('id', 0);
        /** @var Mage_Downloadable_Model_Mysql4_Link_Purchased_Item $linkPurchasedItem */
        $linkPurchasedItem = Mage::getModel('downloadable/link_purchased_item')->load($id, 'link_hash');
        Mage::register('m_purchased_item', $linkPurchasedItem);
        if(!$linkPurchasedItem->getId()) {
            $this->_forward('defaultNoRoute');
            return $this;
        }
        $this->loadLayout();
        $this->renderLayout();
        return $this;
    }

    public function saveAction() {
        $id = $this->getRequest()->getParam('id', 0);
        /** @var Mage_Downloadable_Model_Mysql4_Link_Purchased_Item $linkPurchasedItem */
        $linkPurchasedItem = Mage::getModel('downloadable/link_purchased_item')->load($id, 'link_hash');
        if (!$linkPurchasedItem->getId()) {
            $this->_redirect('');
            return $this;
        }

        $urls = $this->getRequest()->getParam('domain');
        foreach($urls as $x => $url) {
            if(trim($url) === "") {
                unset($urls[$x]);
                continue;
            }

            if (filter_var($url, FILTER_VALIDATE_URL) === false) {
                Mage::getSingleton('core/session')->addError(sprintf(Mage::helper('local_manadev')->__("Invalid URL: %s"), $url));
                $this->_redirect('*/*/register', array('id' => $id));
                return $this;
            }
        }

        $domain = implode(",", $urls);

        $newZipFilename = $this->_createNewZipFileWithLicense($linkPurchasedItem);

        $linkPurchasedItem
            ->setData('m_registered_domain', $domain)
            ->setData('status', Local_Manadev_Model_Download_Status::M_LINK_STATUS_AVAILABLE)
            ->setData('link_file', $newZipFilename)
            ->save();

        /* @var $product Mage_Catalog_Model_Product */ $product = Mage::getModel(strtolower('catalog/product'));
        $productId = $linkPurchasedItem->getData('product_id');
        $product->load($productId);

        if (!$product->getId()) throw new Mage_Core_Exception($this->__('Product %d does not exist', $productId));

        $this->_getCustomerSession()
            ->addSuccess('Thank you for registering your domain. Your product download shall start automatically.')
            ->setData('m_pending_download_link_hash', $linkPurchasedItem->getLinkHash());

        if ($installationInstructionUrl = $product->getData('installation_instruction_url')) {
            $this->_redirect('', array('_direct' => ltrim($installationInstructionUrl, '/')));
        } else {
            $this->_redirect('downloadable/customer/products');
        }


        return $this;
    }

    protected function _createNewZipFileWithLicense($linkPurchasedItem) {
        /* @var $storage Mage_Downloadable_Helper_File */
        $storage = Mage::helper('downloadable/file');
        $id = $linkPurchasedItem->getId();
        $resource = $storage->getFilePath(Mage_Downloadable_Model_Link::getBasePath(), $linkPurchasedItem->getLinkFile());

        $pathinfo = pathinfo($resource);

        $newZipFilename = $pathinfo['dirname'] . DS . $pathinfo['filename'] . "-" . $id . "." . $pathinfo['extension'];
        copy($resource, $newZipFilename);
        $zip = new ZipArchive();
        if ($zip->open($newZipFilename) === true) {
            $licenseDir = "app/code/local/Mana/Core/license";
            $zip->addEmptyDir($licenseDir);
            $zip->addFromString("{$licenseDir}/{$id}", $id);
            $zip->close();
        }
        return str_replace(Mage_Downloadable_Model_Link::getBasePath(), "", $newZipFilename);
    }

    /**
     * Returns object containing current user's customer data
     * @return Mage_Customer_Model_Session
     */
    protected function _getCustomerSession() {
        return Mage::getSingleton('customer/session');
    }
}