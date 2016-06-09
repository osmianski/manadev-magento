<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Model_Downloadable_Item extends Mage_Downloadable_Model_Link_Purchased_Item
{
    const LICENSE_NO_SALT = '3jY65MRSVsCrnmuU';
    const LICENSE_VERIFICATION_SALT = '6BCRWtJJp8GsEBmy';
    const ENTITY = 'downloadable/link_purchased_item';
    protected $_frontendLabel;
    protected $_product;

    public function getFrontendLabel() {
        if(!$this->_frontendLabel) {
            $purchased = Mage::getModel('downloadable/link_purchased')->load($this->getPurchasedId());
            $this->_frontendLabel = $purchased->getOrderIncrementId() . ' --- ' . $purchased->getProductName();
        }

        return $this->_frontendLabel;
    }

    /**
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct(){
        if(!$this->_product) {
            $this->_product = Mage::getModel('catalog/product')->load($this->getProductId());
        }

        return $this->_product;
    }

    public function getProductName() {
        return $this->getProduct()->getName();
    }

    public function getFormattedSupportExpiry() {
        return date("F j, Y", strtotime($this->getData('m_support_valid_til')));
    }

    public function getRegisteredDomain() {
        $domain = $this->getData('m_registered_domain');
        if (trim($domain) == "") {
            $domain = Mage::helper('local_manadev')->__("(None)");
        }

        return $domain;
    }

    public function _beforeSave() {
        $result = $this->_baseBeforeSave();

        if(!$this->getId()) {
            $this->setData('m_license_verification_no', $this->generateLicenseVerificationNo())
                ->setData('m_license_no', $this->generateLicenseNumber());

            $date = Mage::app()->getLocale()->date(
                Varien_Date::toTimestamp($this->getCreatedAt()),
                null,
                null,
                true
            );
            $expire_date = strtotime("+6 months", $date->getTimestamp());
            $expire_date = date("Y-m-d", $expire_date);
            $this->setData('m_support_valid_til', $expire_date);
        }

        return $result;
    }

    public function generateLicenseNumber() {
        return $this->_generateKey("L", self::LICENSE_NO_SALT);
    }

    public function generateLicenseVerificationNo() {
        return $this->_generateKey("V", self::LICENSE_VERIFICATION_SALT);
    }

    protected function _generateKey($prefix, $salt) {
        /** @var Mage_Downloadable_Model_Link_Purchased $puchasedModel */
        $puchasedModel = Mage::getModel('downloadable/link_purchased')->load($this->getPurchasedId());
        /** @var Mage_Customer_Model_Customer $customerModel */
        $customerModel = Mage::getModel('customer/customer')->load($puchasedModel->getCustomerId());
        $x = $puchasedModel->getOrderIncrementId() . '|' . $customerModel->getEmail() . '|' . $this->getId() . '|' . $salt;
        $licenseNo = $this->_getKeyModel()->shaToLicenseNo(sha1($x));
        $licenseNo = $prefix .
            substr($licenseNo, 0, 5) . '-' .
            substr($licenseNo, 5, 6) . '-' .
            substr($licenseNo, 11, 5) . '-' .
            substr($licenseNo, 16, 6) . '-' .
            substr($licenseNo, 22, 5);

        return $licenseNo;
    }

    /**
     * @return Local_Manadev_Model_Key
     */
    protected function _getKeyModel() {
        return Mage::getModel('local_manadev/key');
    }


    public function afterCommitCallback() {
        parent::afterCommitCallback();
        if (!Mage::registry('m_prevent_indexing_on_save')) {
            $this->getIndexerSingleton()->processEntityAction($this, static::ENTITY,
                Mage_Index_Model_Event::TYPE_SAVE);
        }
        return $this;
    }

    /**
     * @return Mage_Index_Model_Indexer
     */
    public function getIndexerSingleton() {
        return Mage::getSingleton('index/indexer');
    }

    protected function _baseBeforeSave() {
        if (!$this->getId()) {
            $this->isObjectNew(true);
        }
        Mage::dispatchEvent('model_save_before', array('object' => $this));
        Mage::dispatchEvent($this->_eventPrefix . '_save_before', $this->_getEventData());

        return $this;
    }
}