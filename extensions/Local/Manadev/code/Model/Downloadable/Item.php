<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Model_Downloadable_Item extends Mage_Downloadable_Model_Link_Purchased_Item
{
    const SALT = '3jY65MRSVsCrnmuU';

    public function _beforeSave() {
        $result = parent::_beforeSave();

        if(!$this->getId()) {
            $this->setData('m_license_verification_no', uniqid())
                ->setData('m_license_no', uniqid());

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
        /** @var Mage_Downloadable_Model_Link_Purchased $puchasedModel */
        $puchasedModel = Mage::getModel('downloadable/link_purchased')->load($this->getPurchasedId());
        /** @var Mage_Customer_Model_Customer $customerModel */
        $customerModel = Mage::getModel('customer/customer')->load($puchasedModel->getCustomerId());
        $x = $puchasedModel->getOrderIncrementId() . '|' . $customerModel->getEmail() . '|' . $this->getId() . '|' . self::SALT;
        $licenseNo = $this->_getKeyModel()->shaToLicenseNo(sha1($x));
        $licenseNo = 'L' .
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
}