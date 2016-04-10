<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Model_License_Request extends Mage_Core_Model_Abstract
{
    protected $_extensions;
    protected $_modules;
    protected $_stores;

    protected function _construct() {
        $this->_init('local_manadev/license_request');
    }

    public function setModules($installedModules) {
        $this->_modules = $installedModules;
    }

    public function setExtensions($installedManadevExtensions) {
        $this->_extensions = $installedManadevExtensions;
    }

    public function setStores($stores) {
        $this->_stores = $stores;
    }

    public function getModules() {
        return $this->_modules;
    }

    public function getExtensions() {
        return $this->_extensions;
    }

    public function getStores() {
        return $this->_stores;
    }
}