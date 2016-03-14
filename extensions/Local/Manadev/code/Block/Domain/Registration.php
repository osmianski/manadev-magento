<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Block_Domain_Registration extends Mage_Core_Block_Template
{
    protected $_productModel;
    protected $_purchasedModel;

    public function getProductName() {
        return $this->_getProductModel()->getName();
    }

    /**
     * @return Mage_Downloadable_Model_Link_Purchased_Item
     */
    protected function _getPurchasedItem() {
        return Mage::registry('m_purchased_item');
    }

    /**
     * @param $item
     * @return Mage_Core_Model_Abstract
     */
    protected function _getProductModel() {
        if(!$this->_productModel) {
            $this->_productModel = Mage::getModel('catalog/product')->load($this->_getPurchasedItem()->getProductId());
        }

        return $this->_productModel;
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
        return $this->getUrl('*/*/save', array('id' => $this->_getPurchasedItem()->getLinkHash(), '_secure' => true));
    }
}