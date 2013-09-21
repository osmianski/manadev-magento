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
                ->setFieldExpr($alias->fieldExpr($context, $field))
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

        /* @var $formulaHelper Mana_Db_Helper_Formula */
        $formulaHelper = Mage::helper('mana_db/formula');

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
        $field = $entity.'_id_0';
        if (isset($scopeXml->fields->$field)) {
            $fieldPrefix = $entity . '_id_';
            /* @var $result Mana_Db_Model_Formula_Entity */
            $result = Mage::getModel('mana_db/formula_entity');

            $entityName = (string)$scopeXml->fields->$field->foreign->entity;
            $fields = array();

            $result
                ->setHelper('aggregate_field')
                ->setEntity($entityName)
                ->setProcessor($this->getProcessor($entityName));

            /* @var $core Mana_Core_Helper_Data */
            $core = Mage::helper('mana_core');
            $compositeAlias = array();

            /* @var $joinClosure Mana_Db_Model_Formula_Closure_AggregateFieldJoin */
            $joinClosure = Mage::getModel('mana_db/formula_closure_aggregateFieldJoin', compact('context', 'result'));

            foreach ($scopeXml->fields->children() as $fieldName => $fieldXml) {
                if ($core->startsWith($fieldName, $fieldPrefix)) {
                    $index = (int)substr($fieldName, strlen($fieldPrefix));
                    $context->getAlias()->child($formulaHelper->createAlias($entity . $index))->each($joinClosure
                        ->setTargetIndex($index)
                        ->setEntityName($entityName)
                        ->setFieldXml($fieldXml));
                }
            }
            $compositeAlias = $joinClosure->getCompositeAlias();
            $fields = $joinClosure->getFields();
            ksort($compositeAlias);
            ksort($fields);

            return $result
                ->setAlias($formulaHelper->createAlias($compositeAlias))
                ->setAggregateFields($fields);
        }

        return false;
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Entity $entity
     */
    protected function setForeignJoin($context, $entity) {
        /* @var $joinClosure Mana_Db_Model_Formula_Closure_ForeignJoin */
        $joinClosure = Mage::getModel('mana_db/formula_closure_foreignJoin', compact('context', 'entity'));
        $entity->setForeignJoin($context->getAlias()->each($joinClosure));
    }

    public function getPrimaryKey($entity) {
        return 'id';
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
            /* @var $formulaHelper Mana_Db_Helper_Formula */
            $formulaHelper = Mage::helper('mana_db/formula');

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
            $alias = $context->getAlias()->child($formulaHelper->createAlias($entity));

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