<?php
/**
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Base class for eav resources which can handle store-scoped values as well as default for global scope.
 * @author Mana Team
 *
 */
class Mana_Core_Resource_Eav extends Mage_Eav_Model_Entity_Abstract {
    protected function _construct() {
    	$this->_read = Mage::getSingleton('core/resource')->getConnection('core_read');
    	$this->_write = Mage::getSingleton('core/resource')->getConnection('core_write');
    }
    
    public function loadDefaults($model) {
    	$this->loadAllAttributes();
    	foreach ($this->getAttributesByCode() as $attributeCode => $attribute) {
    		if ($attribute->getHasDefault() && $model->isDefaultValue($attribute)
    			 && (!$model->getStoreId() || !$this->hasStoreValue($model, $attribute))) 
    		{
    			$defaultProvider = Mage::getSingleton($attribute->getDefaultModel());
    			$model->setData($attributeCode, 
    				$defaultProvider->getDefaultValue($model, $attributeCode, $attribute->getDefaultSource()));
    		}
    	}
    	return $this;
    }
    
    protected $_keyAttributes;
    public function getKeyAttributes() {
    	if (!$this->_keyAttributes) {
	        $this->_keyAttributes = Mage::getResourceModel($this->getEntityType()->getEntityAttributeCollection())
	        	->addIsKeyFilter();
    	}
    	return $this->_keyAttributes;
    }
	/**
	 * Checks whether entity has attribute value for specific store
	 * @param Mana_Core_Model_Eav $model
	 * @param Mage_Eav_Model_Entity_Attribute $attribute
	 * @param int $storeId
	 */
	public function hasStoreValue($model, $attribute) {
		return $this->getStoreValueId($model, $attribute) != false;
	}
	public function getStoreValueId($model, $attribute) {
		if ($model->getId()) {
			return $this->getReadConnection()->fetchOne("SELECT value_id FROM {$attribute->getBackendTable()} WHERE 
				(entity_id = {$model->getId()}) AND (attribute_id = {$attribute->getId()}) AND (store_id = {$model->getStoreId()})");
		}
		else {
			return false;
		}
	}
	protected function _saveAttribute($object, $attribute, $value) {
        $table = $attribute->getBackend()->getTable();
        if (!isset($this->_attributeValuesToSave[$table])) {
            $this->_attributeValuesToSave[$table] = array();
        }

        $entityIdField = $attribute->getBackend()->getEntityIdField();

        $data   = array(
            'entity_type_id'    => $object->getEntityTypeId(),
            $entityIdField      => $object->getId(),
            'attribute_id'      => $attribute->getId(),
            'store_id'			=> $object->getStoreId(),
            'value'             => $this->_prepareValueForSave($value, $attribute)
        );

        $this->_attributeValuesToSave[$table][] = $data;

        return $this;
	}
	protected function _collectSaveData($newObject) {
		// when deleting store specific values, value ids should be in place
		if ($newObject->getStoreId()) {
			foreach ($this->getAttributesByCode() as $code => $attribute) {
				if ($attribute->getIsGlobal() == Mana_Core_Model_Attribute_Scope::_STORE && is_null($newObject->getData($code))) {
					$attribute->getBackend()->setValueId($this->getStoreValueId($newObject, $attribute));
				}
			}
		} 
		
		return parent::_collectSaveData($newObject);
	}
	
}