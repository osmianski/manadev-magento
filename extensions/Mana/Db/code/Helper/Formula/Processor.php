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

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Varien_Simplexml_Element $xml
     * @param string $entity
     * @param string $mode
     * @return Mana_Db_Model_Formula_Entity
     */
    protected function _selectEntityBasedOnXml($context, $xml, $entity, $mode) {
        if (isset($xml->$entity)) {
            /* @var $entityXml Varien_Simplexml_Element */
            /** @noinspection PhpUndefinedFieldInspection */
            $entityXml = $xml->$entity;

            /* @var $result Mana_Db_Model_Formula_Entity */
            $result = Mage::getModel('mana_db/formula_entity');

            if ($entity == 'primary') {
                /* @var $dbConfig Mana_Db_Helper_Config */
                $dbConfig = Mage::helper('mana_db/config');

                $scopeXml = $dbConfig->getScopeXml($context->getEntity());
                /** @noinspection PhpUndefinedFieldInspection */
                $entityName = (string)$scopeXml->flattens;
            }
            else {
                /** @noinspection PhpUndefinedFieldInspection */
                $entityName = (string)$entityXml->entity;
            }
            $data = $entityXml->asArray();
            $alias = $context->getAlias() ? $context->getAlias() . '.' . $entity : $entity;
            return $result
                ->setHelper($mode)
                ->setAlias($alias)
                ->setEntity($entityName)
                ->setProcessor($this->getProcessor($entityName))
                ->addData($data ? $data : array());
        }
        else {
            return false;
        }
    }
}