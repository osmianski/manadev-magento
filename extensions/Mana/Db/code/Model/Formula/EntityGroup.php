<?php
/** 
 * @category    Mana
 * @package     Mana_Db
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 * @method string getName()
 * @method Mana_Db_Model_Formula_EntityGroup setName(string $value)
 * @method int getSortOrder()
 * @method Mana_Db_Model_Formula_EntityGroup setSortOrder(int $value)
 */
class Mana_Db_Model_Formula_EntityGroup extends Varien_Object {
    /**
     * @var Mana_Db_Model_Formula_Entity[]
     */
    protected $_entities = array();

    /**
     * @param Mana_Db_Model_Formula_Entity $entity
     * @return Mana_Db_Model_Formula_EntityGroup
     */
    public function addEntity($entity) {
        $this->_entities[$entity->getName()] = $entity;
        return $this;
    }
}