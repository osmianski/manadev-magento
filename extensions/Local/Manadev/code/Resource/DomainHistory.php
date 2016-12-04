<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Resource_DomainHistory extends Mage_Core_Model_Mysql4_Abstract
{
    public function insertHistory($itemId, $domain, $storeInfo) {
        $this->_getWriteAdapter()->insert($this->getMainTable(),
        array(
            'item_id' => $itemId,
            'm_registered_domain' => $domain,
            'm_store_info' => $storeInfo
        ));
    }

    /**
     * Resource initialization
     */
    protected function _construct() {
        $this->_init('local_manadev/domainHistory', 'id');
    }
}