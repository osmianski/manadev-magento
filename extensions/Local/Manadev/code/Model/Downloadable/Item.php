<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Model_Downloadable_Item extends Mage_Downloadable_Model_Link_Purchased_Item
{
    public function _beforeSave() {
        $result = parent::_beforeSave();

        if(!$this->getId()) {
            $this->setData('m_license_verification_no', uniqid())
                ->setData('m_license_no', uniqid());
        }

        return $result;
    }
}