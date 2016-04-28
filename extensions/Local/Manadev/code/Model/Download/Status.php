<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Model_Download_Status
{
    const M_LINK_STATUS_AVAILABLE = 'available';
    const M_LINK_STATUS_NOT_AVAILABLE = 'expired'; // `expired` status has been reused and treated as not available for download. This happens when the product is refunded.
    const M_LINK_STATUS_AVAILABLE_TIL = 'available_til';
    const M_LINK_STATUS_PERIOD_EXPIRED = 'period_expired';
    const M_LINK_STATUS_DOWNLOAD_EXPIRED = 'download_expired'; // When download is expired, it is assumed that support is also expired.
    const M_LINK_STATUS_NOT_REGISTERED = 'not_registered';

    public $statuses = array(
        self::M_LINK_STATUS_AVAILABLE => 'Available',
        self::M_LINK_STATUS_NOT_AVAILABLE => 'Not Available',
        self::M_LINK_STATUS_AVAILABLE_TIL => 'Available until XX',
        self::M_LINK_STATUS_PERIOD_EXPIRED => 'Support Period Expired',
        self::M_LINK_STATUS_DOWNLOAD_EXPIRED => 'Download and Support Expired',
        self::M_LINK_STATUS_NOT_REGISTERED => 'Not Registered',
    );

    public function getStatusLabel($status, $item = array()) {
        if(!isset($this->statuses[$status])) {
            return false;
        }

        $label = Mage::helper('local_manadev')->__($this->statuses[$status]);

        if(isset($item['m_support_valid_til']) && $status == self::M_LINK_STATUS_AVAILABLE_TIL) {
            $supportValidTil = $item['m_support_valid_til'];
            $formattedDate = date("F j, Y", strtotime($supportValidTil));
            $label = str_replace("XX", $formattedDate, $label);
        }

        return $label;
    }

    public function toOptionArray(){
        return $this->statuses;
    }
}