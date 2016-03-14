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

        $this->_redirect('downloadable/customer/products', array('download' => $linkPurchasedItem->getLinkHash()));

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
}