<?php
/**
 * @category    Mana
 * @package     Mana_GeoLocation
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * Generic helper functions for Mana_GeoLocation module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class Mana_GeoLocation_Helper_Data extends Mage_Core_Helper_Abstract {
    public function find($search) {
        if (strpos($search, '@') !== false) {
            /* @var $resource Mana_GeoLocation_Resource_Domain */
            $resource = Mage::getResourceModel('mana_geolocation/domain');
            return $resource->findCountryIdByInternetAddress($search);
        }
        else {
            /* @var $resource Mana_GeoLocation_Resource_Ip4 */
            $resource = Mage::getResourceModel('mana_geolocation/ip4');
            return $resource->findCountryIdByIp($search);
        }
    }
}