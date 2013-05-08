<?php
/** 
 * @category    Mana
 * @package     Mana_Seo
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @method string getStatus()
 * @method Mana_Seo_Model_Url setStatus(string $value)
 * @method string getType()
 * @method Mana_Seo_Model_Url setType(string $value)
 */
class Mana_Seo_Model_Url extends Mana_Db_Model_Entity {
    const STATUS_ACTIVE = 'active';
    const STATUS_OBSOLETE = 'obsolete';
    const STATUS_DISABLED = 'disabled';

    /**
     * @return Mana_Seo_Helper_Url
     */
    public function getHelper() {
        return Mage::helper($this->getType());
    }
}