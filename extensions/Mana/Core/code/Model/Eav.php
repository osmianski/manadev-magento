<?php
/**
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Base class for eav models which can handle store-scoped values as well as default for global scope
 * @author Mana Team
 *
 */
class Mana_Core_Model_Eav extends Mage_Core_Model_Abstract {
    public function loadByAttribute($attribute, $value, $additionalAttributes='*')
    {
        $collection = $this->getResourceCollection()
        	->setStoreId($this->getStoreId())
            ->addAttributeToSelect($additionalAttributes)
            ->addAttributeToFilter($attribute, $value)
            ->setPage(1,1);

        foreach ($collection as $object) {
            return $object;
        }
        return $this->loadDefaults();
    }
	protected function _afterLoad() {
		$this->loadDefaults();
		parent::_afterLoad();
	}
	public function loadDefaults() {
		$this->getResource()->loadDefaults($this);
		return $this;
	}
	public function isDefaultValue($attribute) {
		return (((int)$this->getData($attribute->getDefaultMaskField())) & ((int)$attribute->getDefaultMask())) == 0;
	}
	
	protected $_storeValueFlags;
	public function isStoreValue($attribute) {
		if (!$this->_storeValueFlags) $this->_storeValueFlags = array();
		$key = $attribute->getAttributeCode().'_'.$this->getStoreId();
		if (!isset($this->_storeValueFlags[$key])) {
			$this->_storeValueFlags[$key] = $this->getResource()->hasStoreValue($this, $attribute);
		}
		return $this->_storeValueFlags[$key];
	}
}