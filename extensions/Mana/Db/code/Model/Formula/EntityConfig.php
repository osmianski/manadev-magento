<?php
/** 
 * @category    Mana
 * @package     Mana_Db
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 * @method Mana_Db_Model_Formula_Entity getFlat()
 * @method Mana_Db_Model_Formula_Entity getPrimary()
 */
class Mana_Db_Model_Formula_EntityConfig extends Varien_Object {
    /**
     * @var Mana_Db_Model_Formula_Entity[]
     */
    protected $_foreign = array();

    /**
     * @var Mana_Db_Model_Formula_EntityGroup[]
     */
    protected $_entityGroups = array();

    public function setFlat($value) {
        $this->setData('flat', $this->_prepareEntity('flat', $value, false));
        return $this;
    }

    public function setPrimary($value) {
        $this->setData('primary', $this->_prepareEntity('primary', $value));
        return $this;
    }

    /**
     * @param string $name
     * @param Mana_Db_Model_Formula_Entity | string $value
     * @param string $group
     * @param int $entityOrder
     * @param int $groupOrder
     * @return Mana_Db_Model_Formula_EntityConfig
     */
    public function addForeign($name, $value, $group = 'primary', $entityOrder = 100, $groupOrder = 100) {
        $this->_foreign[$name] = $this->_prepareEntity('primary', $value, $group, $entityOrder, $groupOrder);
        return $this;
    }

    /**
     * @param string $name
     * @return Mana_Db_Model_Formula_Entity | null
     */
    public function getForeign($name) {
        return isset($this->_foreign[$name]) ? $this->_foreign[$name] : null;
    }
    /**
     * @param array $data
     * @return Mana_Db_Model_Formula_Entity
     */
    public function createEntity($data = array()) {
        return Mage::getModel('mana_db/formula_entity', $data);
    }

    /**
     * @param array $data
     * @return Mana_Db_Model_Formula_EntityGroup
     */
    public function createEntityGroup($data = array()) {
        return Mage::getModel('mana_db/formula_entityGroup', $data);
    }

    /**
     * @param string $name
     * @param Mana_Db_Model_Formula_Entity | string $entity
     * @param string $group
     * @param int $entityOrder
     * @param int $groupOrder
     * @return Mana_Db_Model_Formula_Entity
     */
    protected function _prepareEntity($name, $entity, $group = 'primary', $entityOrder = 100, $groupOrder = 100) {
        if (is_string($entity)) {
            $entity = $this->createEntity()->setEntity($entity);
        }
        $entity
            ->setName($name)
            ->setSortOrder($entityOrder);

        if ($group) {
            if (!isset($this->_entityGroups[$group])) {
                $this->_entityGroups[$group] = $this->createEntityGroup()
                    ->setName($group)
                    ->setSortOrder($groupOrder);
            }
            $this->_entityGroups[$group]->addEntity($entity);
        }
        return $entity;
    }
}
