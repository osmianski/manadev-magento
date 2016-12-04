<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Resource_DomainHistory_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct() {
        $this->_init('local_manadev/domainHistory');
    }

}