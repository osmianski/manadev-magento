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
 * @method string getManualUrlKey()
 * @method Mana_Seo_Model_Url setManualUrlKey(string $value)
 * @method string getUniqueKey()
 * @method Mana_Seo_Model_Url setUniqueKey(string $value)
 * @method string getFinalUrlKey()
 * @method Mana_Seo_Model_Url setFinalUrlKey(string $value)
 * @method string getType()
 * @method Mana_Seo_Model_Url setType(string $value)
 * @method string getUrlKeyProvider()
 * @method Mana_Seo_Model_Url setUrlKeyProvider(string $value)
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
 * @method int getCmsPageId()
 * @method Mana_Seo_Model_Url setCmsPageId(int $value)
 * @method int getAttributeId()
 * @method Mana_Seo_Model_Url setAttributeId(int $value)
 * @method string getInternalName()
 * @method Mana_Seo_Model_Url setInternalName(string $value)
 * @method bool getForceIncludeFilterName()
 * @method Mana_Seo_Model_Url setForceIncludeFilterName(bool $value)
 * @method bool getIncludeFilterName()
 * @method Mana_Seo_Model_Url setIncludeFilterName(bool $value)
 * @method bool getFinalIncludeFilterName()
 * @method Mana_Seo_Model_Url setFinalIncludeFilterName(bool $value)
 * @method int getOptionId()
 * @method Mana_Seo_Model_Url setOptionId(int $value)
 * @method string getFilterDisplay()
 * @method Mana_Seo_Model_Url setFilterDisplay(string $value)
 * @method int getPosition()
 * @method Mana_Seo_Model_Url setPosition(int $value)
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

    protected function _beforeSave() {
        if ($this->getForceIncludeFilterName() === '') {
            $this->setForceIncludeFilterName(null);
        }
        $this->setFinalIncludeFilterName($this->getForceIncludeFilterName() !== null
            ? $this->getForceIncludeFilterName()
            : $this->getIncludeFilterName());
        $this->setFinalUrlKey($this->getManualUrlKey() !== null && trim($this->getManualUrlKey()) !== ''
            ? $this->getManualUrlKey()
            : $this->getUrlKey());
        parent::_beforeSave();

        return $this;
    }
}