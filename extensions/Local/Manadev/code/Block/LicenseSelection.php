<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Block_LicenseSelection extends Mage_Core_Block_Template
{

    public function __construct(array $args) {
        parent::__construct($args);

        $purchasedItems = $this->_getLocalHelper()->getCustomerLicenseCollection();
        $this->setItems($purchasedItems);
    }

    /**
     * @return Local_Manadev_Helper_Data
     */
    protected function _getLocalHelper() {
        return Mage::helper('local_manadev');
    }
}