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
class Mana_Db_Helper_Formula_Processor_Entity extends Mana_Db_Helper_Formula_Processor {
    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param string $field
     * @return Mana_Db_Model_Formula_TypedExpr | bool
     */
    public function selectField($context, $field) {
        if ($result = parent::selectField($context, $field)) {
            return $result;
        }

        /* @var $dbConfig Mana_Db_Helper_Config */
        $dbConfig = Mage::helper('mana_db/config');

        /* @var $formulaHelper Mana_Db_Helper_Formula */
        $formulaHelper = Mage::helper('mana_db/formula');

        /* @var $fieldsXml Varien_Simplexml_Element */
        /** @noinspection PhpUndefinedFieldInspection */
        $fieldsXml = $dbConfig->getScopeXml($context->getEntity())->fields;

        if (isset($fieldsXml->$field)) {
            if (!($alias = $context->getAlias()) || $alias == 'this') {
                $alias = 'primary';
            }
            return $context->getHelper()->expr()
                ->setExpr("`$alias`.`$field`")
                ->setType((string)$fieldsXml->$field->type)
                ->setIsAggregate($context->getIsAggregate());
        }
        else {
            return false;
        }
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param string $entity
     * @return Mana_Db_Model_Formula_Context | bool
     */
    public function selectEntity($context, $entity) {
        if ($result = parent::selectEntity($context, $entity)) {
            return $result;
        }

        /* @var $dbConfig Mana_Db_Helper_Config */
        $dbConfig = Mage::helper('mana_db/config');

        /* @var $scopeXml Varien_Simplexml_Element */
        /** @noinspection PhpUndefinedFieldInspection */
        $aggregateXml = $dbConfig->getScopeXml($context->getEntity())->formula->aggregate;
        if (isset($aggregateXml->$entity)) {
            $entity = (string)$aggregateXml->$entity->entity;
            $processor = 'entity';
            $result = clone $context;
            $result
                ->setEntity($entity)
                ->setProcessor($processor)
                ->setIsAggregate(true)
                ->setParentContext($context);
        }
    }

    /**
     * @return Varien_Db_Select
     */
    public function getAggregateSql($aggregateContext) {
        /* @var $res Mage_Core_Model_Resource */
        $res = Mage::getSingleton('core/resource');

        /* @var $db Varien_Db_Adapter_Pdo_Mysql */
        $db = $res->getConnection('read');

        $sql = $db->select();


        return $sql;
    }

}