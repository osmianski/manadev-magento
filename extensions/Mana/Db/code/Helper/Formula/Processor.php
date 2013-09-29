<?php
/** 
 * @category    Mana
 * @package     Mana_Db
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
abstract class Mana_Db_Helper_Formula_Processor extends Mage_Core_Helper_Abstract {
    protected static $_eavEntityTypes = array();

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param string $field
     * @return Mana_Db_Model_Formula_Expr | bool
     */
    public function selectField(/** @noinspection PhpUnusedParameterInspection */$context, $field) {
        return false;
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param string $entity
     * @return Mana_Db_Model_Formula_Entity | bool
     */
    public function selectEntity(/** @noinspection PhpUnusedParameterInspection */$context, $entity) {
        return false;
    }

    abstract public function getPrimaryKey($entity);

    public function getProcessor($entity) {
        /* @var $dbConfig Mana_Db_Helper_Config */
        $dbConfig = Mage::helper('mana_db/config');

        if ($dbConfig->getScopeXml($entity)) {
            return 'entity';
        }
        elseif ($this->getEavEntityType($entity)) {
            return 'eav';
        }
        else {
            return 'table';
        }
    }

    /**
     * @param string $entity
     * @return Mage_Eav_Model_Entity_Type | bool
     */
    public function getEavEntityType($entity) {
        if (!isset(self::$_eavEntityTypes[$entity])) {
            /* @var $entityType Mage_Eav_Model_Entity_Type */
            $entityType = Mage::getModel('eav/entity_type');
            $entityType->load($entity, 'entity_model');
            self::$_eavEntityTypes[$entity] = $entityType->getId() ? $entityType : false;
        }
        return self::$_eavEntityTypes[$entity];
    }
}