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
class Mana_GeoLocation_Resource_Ip4_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
    protected function _construct() {
        $this->_init('mana_geolocation/ip4');
    }
}