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
     * @return Mana_Db_Model_Formula_Expr | bool
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
            $alias = $context->getAlias();
            return $context->getHelper()->expr()
                ->setFieldExpr($context->resolveAlias("$alias.$field"))
                ->setFieldName($field)
                ->setType((string)$fieldsXml->$field->type);
        }
        else {
            return false;
        }
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param string $entity
     * @return Mana_Db_Model_Formula_Entity | bool
     */
    public function selectEntity($context, $entity) {
        if ($aggregateContext = parent::selectEntity($context, $entity)) {
            return $aggregateContext;
        }

        /* @var $dbConfig Mana_Db_Helper_Config */
        $dbConfig = Mage::helper('mana_db/config');

        $scopeXml = $dbConfig->getScopeXml($context->getEntity());
        if ($result = $this->_selectEntityBasedOnXml($context, $scopeXml->formula->base->from, $entity, 'foreign')) {
            $this->setForeignJoin($context, $result);
            return $result;
        }
        if ($result = $this->_selectEntityBasedOnXml($context, $scopeXml->formula->base->join, $entity, 'foreign')) {
            $this->setForeignJoin($context, $result);
            return $result;
        }
        if ($result = $this->_selectEntityBasedOnXml($context, $scopeXml->formula->aggregate, $entity, 'aggregate')) {
            return $result;
        }
        if ($result = $this->_selectEntityBasedOnXml($context, $scopeXml->formula->frontend, $entity, 'frontend')) {
            return $result;
        }

        return false;
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Entity $entity
     */
    protected function setForeignJoin($context, $entity) {
        /* @var $dbConfig Mana_Db_Helper_Config */
        $dbConfig = Mage::helper('mana_db/config');

        if ($context->getAlias()) {
            $entity->setForeignJoin("{{= {$entity->getAlias()}.{$entity->getProcessor()->getPrimaryKey($entity->getEntity())} }} = " .
                "{{= {$context->getAlias()}.{$dbConfig->getForeignKey($entity->getEntity(), $context->getEntity())} }}");
        }
    }

    public function getPrimaryKey($entity) {
        return 'id';
    }
}