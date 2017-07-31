<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Model_Composer_Repo extends Mage_Core_Model_Abstract
{
    protected $_extensions = [];

    public function addExtension($extension) {
        $this->_extensions[] = $extension;
    }

    public function getExtensions() {
        return $this->_extensions;
    }

    protected function _construct() {
        $this->_init('local_manadev/composer_repo');
    }

    public function getVersion() {
        $result = '1.0';

        foreach ($this->_extensions as $extension) {
            if (version_compare($result, $extension->getData('version')) < 0) {
                $result = $extension->getData('version');
            }
        }

        return $result;
    }
}