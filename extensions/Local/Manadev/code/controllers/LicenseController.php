<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_LicenseController extends Mana_Admin_Controller_V2_Controller
{
    public function issuedLicensesAction() {
        $this->loadLayout();
        $this->_title('Mana')->_title($this->__("Issued Licenses"));
        $this->_setActiveMenu('mana/licenses/issued_licenses');
        $this->renderLayout();
    }

    public function issuedLicensesGridAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function magentoInstancesAction() {
        $this->loadLayout();
        $this->_title('Mana')->_title($this->__("Magento Instances"));
        $this->_setActiveMenu('mana/licenses/magento_instances');
        $this->renderLayout();
    }

    public function magentoInstancesGridAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function magentoInstanceHistoryAction() {
        $this->loadLayout();
        $this->_title('Mana')->_title($this->__("Magento Instance History"));
        $this->_setActiveMenu('mana/licenses/magento_instance_history');
        $this->renderLayout();
    }

    public function magentoInstanceHistoryGridAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function saveLicenseInfoAction() {
        $id = $this->getRequest()->getParam('id');
        $status = $this->getRequest()->getParam('status');
        $expireDate = $this->getRequest()->getParam('expireDate');
        $registeredUrl = $this->getRequest()->getParam('registeredDomain');
        $storeInfo = $this->getRequest()->getParam('storeInfo');
        $insertHistory = $this->getRequest()->getParam('insertHistory');

        $purchasedItem = Mage::getModel('downloadable/link_purchased_item')->load($id);

        if($expireDate) {
            $expireDate = Varien_Date::formatDate($expireDate, false);
        } else {
            $expireDate = null;
        }

        if($insertHistory == "true") {
            $purchasedItem->setData('m_registered_domain', $registeredUrl);
            $purchasedItem->setData('m_store_info', $storeInfo);

            /** @var Local_Manadev_Resource_DomainHistory $dhResource */
            $dhResource = Mage::getResourceModel('local_manadev/domainHistory');
            $dhResource->insertHistory($purchasedItem->getId(), $purchasedItem->getData('m_registered_domain'), $purchasedItem->getData('m_store_info'));

            $dhCollection = $this->localHelper()->prepareDomainHistoryCollection($id);

            $html = $this->localHelper()->getDomainHistoryHtml($dhCollection);
        }

        $purchasedItem
            ->setData('status', $status)
            ->setData('m_support_valid_til', $expireDate)
            ->save();

        $response = array('success' => true, 'message' => $this->_getHelper()->__("Issued license information successfully updated!"));

        if(isset($html)) {
            $response['m_registered_domain_history'] = $html;
        }
        $response['new_status'] = Mage::getModel('downloadable/link_purchased_item')->load($id)->getStatus();

        $this->getResponse()->setBody(json_encode($response));
    }

    /**
     * @return Local_Manadev_Helper_Data
     */
    protected function localHelper() {
        return Mage::helper('local_manadev');
    }
}