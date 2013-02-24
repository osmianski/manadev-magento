<?php
/**
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Attribute collection with exetnded info
 * @author Mana Team
 *
 */
class Mana_Core_Resource_Attribute_Collection extends Mage_Eav_Model_Mysql4_Entity_Attribute_Collection
{
	protected $_entityType;
	public function getEntityType() {
		return $this->_entityType;
	}
	public function setEntityType($value) {
		$this->_entityType = $value;
		return $this;
	}
    protected function _initSelect()
    {
        $this->getSelect()->from(array('main_table' => $this->getResource()->getMainTable()))
            ->where('main_table.entity_type_id=?', 
            	Mage::getModel('eav/entity')->setType($this->getEntityType())->getTypeId())
            ->join(
                array('additional_table' => $this->getTable('mana_core/attribute')),
                'additional_table.attribute_id=main_table.attribute_id'
            );
        return $this;
    }

    /**
     * Specify attribute entity type filter
     *
     * @param   int $typeId
     * @return  Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Attribute_Collection
     */
    public function setEntityTypeFilter($typeId)
    {
        return $this;
    }
    
    public function addIsKeyFilter() {
    	$this->getSelect()->where('additional_table.is_key = 1');
    	return $this;
    }
}
