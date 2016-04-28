<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_LicenseController extends Mana_Admin_Controller_V2_Controller
{
    public function issuedLicensesAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function issuedLicensesGridAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function magentoInstancesAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function magentoInstancesGridAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function magentoInstanceHistoryAction() {
        $this->loadLayout();
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

        $purchasedItem = Mage::getModel('downloadable/link_purchased_item')->load($id);

        if($expireDate) {
            $expireDate = Varien_Date::formatDate($expireDate, false);
        } else {
            $expireDate = null;
        }
        $purchasedItem
            ->setData('status', $status)
            ->setData('m_support_valid_til', $expireDate)
            ->save();

        $response = array('success' => true, 'message' => $this->_getHelper()->__("Issued license information successfully updated!"));

        $this->getResponse()->setBody(json_encode($response));
    }
}