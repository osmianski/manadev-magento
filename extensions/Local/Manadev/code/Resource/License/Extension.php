<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Resource_License_Extension extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct() {
        $this->_init('local_manadev/license_extension', 'id');
    }

}