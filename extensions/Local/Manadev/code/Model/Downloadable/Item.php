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
        // Return empty string instead of "(None)"
//        if (trim($domain) == "") {
//            $domain = Mage::helper('local_manadev')->__("(None)");
//        }

        return $domain;
    }

    public function getStoreInfo() {
        return $this->getData('m_store_info');
    }

    public function getRegistrationHistoryHtml() {
        $historyCollection = $this->localHelper()->prepareDomainHistoryCollection($this->getId());
        $html = "";

        if ($historyCollection->count() >= 2) {
            $skipFirst = true;
            /** @var Local_Manadev_Model_DomainHistory $item */
            foreach($historyCollection->getItems() as $item) {
                if($skipFirst) {
                    $skipFirst = false;
                    continue;
                }
                $html .= "&nbsp;&nbsp;&nbsp;&nbsp;" . htmlentities($item->getItemString()) . " <br/>";
            }
        }

        return $html;
    }

    public function _beforeSave() {
        if($this->_isSupportLastPurchasedDateChanged()) {
            // If `m_support_last_purchased_at` changed, update `m_support_valid_til` as well.
            $new_support_valid_date = date('Y-m-d', strtotime("+6 months", Varien_Date::toTimestamp($this->getData('m_support_last_purchased_at'))));
            $this->setData('m_support_valid_til', $new_support_valid_date);
        }

        // Do not return parent _beforeSave(), because it will fail when a free extension is downloaded
        return $this->_baseBeforeSave();
    }

    protected function _afterSave() {
        if($this->isObjectNew()) {
            Mage::getModel('local_manadev/downloadable_item')
                ->load($this->getId())
                ->setLicenseNumbers()
                ->save();
            $this->load($this->getId());
        }

        return parent::_afterSave();
    }

    public function generateLicenseNumber() {
        return $this->_generateKey("L", self::LICENSE_NO_SALT);
    }

    public function generateLicenseVerificationNo() {
        return $this->_generateKey("V", self::LICENSE_VERIFICATION_SALT);
    }

    protected function _generateKey($prefix, $salt) {
        $orderIncrementId = "";
        $customerId = $this->getMFreeCustomerId();
        if($this->getPurchasedId()) {
            /** @var Mage_Downloadable_Model_Link_Purchased $puchasedModel */
            $puchasedModel = Mage::getModel('downloadable/link_purchased')->load($this->getPurchasedId());
            $customerId = $puchasedModel->getCustomerId();
            $orderIncrementId = $puchasedModel->getOrderIncrementId();
        }

        /** @var Mage_Customer_Model_Customer $customerModel */
        $customerModel = Mage::getModel('customer/customer')->load($customerId);
        $x = $orderIncrementId . '|' . $customerModel->getEmail() . '|' . $this->getId() . '|' . $salt;
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

    public function setLicenseNumbers() {
        $this->setData('m_license_verification_no', $this->generateLicenseVerificationNo())
            ->setData('m_license_no', $this->generateLicenseNumber());

        $expire_date = strtotime("+6 months", strtotime($this->getData('m_support_last_purchased_at')));
        $expire_date = date("Y-m-d", $expire_date);
        $this->setData('m_support_valid_til', $expire_date);

        return $this;
    }

    /**
     * @return false|string
     */
    protected function _formatDate($date) {
        return date('Y-m-d', Varien_Date::toTimeStamp($date));
    }

    /**
     * @return bool
     */
    protected function _isSupportLastPurchasedDateChanged() {
        return $this->_formatDate($this->getData('m_support_last_purchased_at')) != $this->_formatDate($this->getOrigData('m_support_last_purchased_at'));
    }

    /**
     * @return Local_Manadev_Helper_Data
     */
    protected function localHelper() {
        return Mage::helper('local_manadev');
    }
}