<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Model_Download_Status
{
    const M_LINK_STATUS_AVAILABLE = 'available'; // For Magento 1. When m_support_valid_til is current date, the status becomes `period_expired`. Customer can still download
    const M_LINK_STATUS_NOT_AVAILABLE = 'expired'; // `expired` status has been reused and treated as not available for download. This happens when the product is refunded.
    const M_LINK_STATUS_AVAILABLE_TIL = 'available_til'; // For Magento 2. When m_support_valid_til is current date, the status becomes `download_expired`
    const M_LINK_STATUS_PERIOD_EXPIRED = 'period_expired';
    const M_LINK_STATUS_DOWNLOAD_EXPIRED = 'download_expired'; // When download is expired, it is assumed that support is also expired.
    const M_LINK_STATUS_NOT_REGISTERED = 'not_registered';

    public $statuses = array(
        self::M_LINK_STATUS_AVAILABLE => 'Available. Support Period Expires at XX',
        self::M_LINK_STATUS_NOT_AVAILABLE => 'Not Available',
        self::M_LINK_STATUS_AVAILABLE_TIL => 'Available until XX',
        self::M_LINK_STATUS_PERIOD_EXPIRED => 'Available. Support Period Expired',
        self::M_LINK_STATUS_DOWNLOAD_EXPIRED => 'Download and Support Expired',
        self::M_LINK_STATUS_NOT_REGISTERED => 'Not Registered',
    );

    public function getStatusLabel($status, $item = array()) {
        if(!isset($this->statuses[$status])) {
            return false;
        }

        $label = $this->_getHelper()->__($this->statuses[$status]);

        if(isset($item['m_support_valid_til']) && strpos($label, "XX") !== false) {
            $supportValidTil = $item['m_support_valid_til'];
            $formattedDate = date("F j, Y", strtotime($supportValidTil));
            $label = str_replace("XX", $formattedDate, $label);
        }

        return $label;
    }

    public function toOptionArray(){
        $result = array();
        foreach($this->statuses as $status => $label) {
            $result[$status] = $this->_getHelper()->__($label);
        }

        return $result;
    }

    /**
     * @return Local_Manadev_Helper_Data
     */
    protected function _getHelper() {
        return Mage::helper('local_manadev');
    }
}