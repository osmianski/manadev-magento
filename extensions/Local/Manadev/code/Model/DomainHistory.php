<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Model_DomainHistory extends Mage_Core_Model_Abstract
{
    protected function _construct() {
        $this->_init('local_manadev/domainHistory');
    }

    public function getItemString() {
        $domain = $this->getData('m_registered_domain');
        if (trim($domain) != "") {
            $item = "URL: " . $domain;
        } else {
            $item = "INFO: " . $this->getData('m_store_info');
        }

        return $item;
    }
}