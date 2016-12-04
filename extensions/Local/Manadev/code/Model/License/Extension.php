<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Model_License_Extension extends Mage_Core_Model_Abstract
{
    protected $_request;

    protected function _construct() {
        $this->_init('local_manadev/license_extension');
    }

    /**
     * @return Local_Manadev_Model_License_Request
     */
    public function getRequest() {
        if (!$this->_request) {
            $this->_request = Mage::getModel('local_manadev/license_request')->load($this->getData('request_id'));
        }

        return $this->_request;
    }
}