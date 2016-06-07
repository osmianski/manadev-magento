<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Block_Support_TicketForm extends Mage_Core_Block_Template
{
    protected $_productModel;
    protected $_purchasedModel;
    protected $_customerModel;

    public function getProductName() {
        return $this->_getProductModel()->getName();
    }

    /**
     * @return Local_Manadev_Model_Downloadable_Item
     */
    protected function _getPurchasedItem() {
        return Mage::registry('m_purchased_item');
    }

    /**
     * @param $item
     * @return Mage_Core_Model_Abstract
     */
    protected function _getProductModel() {
        return $this->_getPurchasedItem()->getProduct();
    }

    public function _getPurchasedModel() {
        if (!$this->_purchasedModel) {
            $this->_purchasedModel = Mage::getModel('downloadable/link_purchased')->load($this->_getPurchasedItem()->getPurchasedId());
        }

        return $this->_purchasedModel;
    }

    public function getOrderNo() {
        return $this->_getPurchasedModel()->getData('order_increment_id');
    }

    public function getFormAction() {
        return $this->getUrl('*/*/submit', array('id' => $this->_getPurchasedItem()->getLinkHash(), '_secure' => true));
    }

    public function getIssueDetails() {
        return $this->_getSession()->getData('post_data/issue_details');
    }

    /**
     * @return Mage_Core_Model_Session
     */
    protected function _getSession() {
        return Mage::getSingleton('core/session');
    }

    public function getCustomerName() {
        return $this->_getCustomerModel()->getName();
    }

    /**
     * @return Mage_Customer_Model_Customer
     */
    public function _getCustomerModel() {
        if(!$this->_customerModel) {
            $this->_customerModel = Mage::getModel('customer/customer')->load($this->_getPurchasedModel()->getCustomerId());
        }

        return $this->_customerModel;
    }

    public function getEmail() {
        return $this->_getCustomerModel()->getEmail();
    }

    public function getSupportValidTil() {
        $date = $this->_getPurchasedItem()->getData('m_support_valid_til');

        return date('F j, Y', strtotime($date));
    }

    public function getRegisteredURL() {
        return $this->_getPurchasedItem()->getRegisteredDomain();
    }
}