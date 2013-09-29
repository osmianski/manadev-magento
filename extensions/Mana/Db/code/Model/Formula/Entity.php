<?php
/** 
 * @category    Mana
 * @package     Mana_Db
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 * @method Mana_Db_Helper_Formula_Entity getHelper()
 * @method Mana_Db_Model_Formula_Alias getAlias()
 * @method Mana_Db_Model_Formula_Entity setAlias(Mana_Db_Model_Formula_Alias $value)
 * @method string getForeignJoin()
 * @method Mana_Db_Model_Formula_Entity setForeignJoin(string $value)
 * @method string getEntity()
 * @method Mana_Db_Model_Formula_Entity setEntity(string $value)
 * @method string[] | bool getJoin()
 * @method Mana_Db_Model_Formula_Entity setJoin(array $value)
 * @method string getOrder()
 * @method Mana_Db_Model_Formula_Entity setOrder(string $value)
 * @method string getWhere()
 * @method Mana_Db_Model_Formula_Entity setWhere(string $value)
 * @method Mana_Db_Helper_Formula_Processor getProcessor()
 * @method string[] | bool getAggregateFields()
 * @method Mana_Db_Model_Formula_Entity setAggregateFields(array $value)
 */
class Mana_Db_Model_Formula_Entity extends Varien_Object {
    /**
     * @param Mana_Db_Helper_Formula_Entity | string $entity
     * @return Mana_Db_Model_Formula_Entity
     */
    public function setHelper($entity) {
        if (is_string($entity)) {
            $entity = Mage::helper('mana_db/formula_entity_' . $entity);
        }
        $this->setData('helper', $entity);

        return $this;
    }

    /**
     * @param Mana_Db_Helper_Formula_Processor | string $processor
     * @return Mana_Db_Model_Formula_Entity
     */
    public function setProcessor($processor) {
        if (is_string($processor)) {
            $processor = Mage::helper('mana_db/formula_processor_' . $processor);
        }
        $this->setData('processor', $processor);

        return $this;
    }
}