<?php
/**
 * @category    Mana
 * @package     Mana_GeoLocation
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_GeoLocation_Resource_Ip6 extends Mage_Core_Model_Mysql4_Abstract {
    protected function _construct() {
        $this->_init('mana_geolocation/ip6', 'id');
    }

    public function truncate() {
        $this->_getWriteAdapter()->truncateTable($this->getMainTable());
    }

    public function run($sql) {
        $this->_getWriteAdapter()->query($sql);
    }
}