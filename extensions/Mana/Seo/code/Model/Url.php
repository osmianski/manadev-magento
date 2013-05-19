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
 * @method string getUrlKey()
 * @method Mana_Seo_Model_Url setUrlKey(string $value)
 * @method string getType()
 * @method Mana_Seo_Model_Url setType(string $value)
 * @method string getInternalParameterName()
 * @method Mana_Seo_Model_Url setInternalParameterName(string $value)
 * @method string getInternalValueName()
 * @method Mana_Seo_Model_Url setInternalValueName(string $value)
 * @method bool getIsPage()
 * @method Mana_Seo_Model_Url setIsPage(bool $value)
 * @method bool getIsParameter()
 * @method Mana_Seo_Model_Url setIsParameter(bool $value)
 * @method bool getIsValue()
 * @method Mana_Seo_Model_Url setIsValue(bool $value)
 * @method int getCategoryId()
 * @method Mana_Seo_Model_Url setCategoryId(int $value)
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