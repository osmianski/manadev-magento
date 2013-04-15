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
class Mana_Db_Helper_Formula_Processor_Table extends Mana_Db_Helper_Formula_Processor {
    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param string $field
     * @return Mana_Db_Model_Formula_Expr | bool
     */
    public function selectField($context, $field) {
        if ($result = parent::selectField($context, $field)) {
            return $result;
        }

        /* @var $resource Mana_Db_Resource_Formula */
        $resource = Mage::getResourceSingleton('mana_db/formula');

        $fields = $resource->getTableFields($context->getEntity());

        if (isset($fields[$field])) {
            return $context->getHelper()->expr()
                ->setFieldExpr($context->getAlias()->fieldExpr($context, $field))
                ->setFieldName($field)
                ->setType($fields[$field]['DATA_TYPE']);
        }
        else {
            /* @var $dbConfig Mana_Db_Helper_Config */
            $dbConfig = Mage::helper('mana_db/config');

            /* @var $formulaHelper Mana_Db_Helper_Formula */
            $formulaHelper = Mage::helper('mana_db/formula');

            /* @var $fieldsXml Varien_Simplexml_Element */
            /** @noinspection PhpUndefinedFieldInspection */
            $fieldsXml = $dbConfig->getTableXml($context->getEntity())->fields;

            if (isset($fieldsXml->$field)) {
                $alias = $context->getAlias();
                $formula = $fieldsXml->$field->formula;
                $context->resetLocalAliases();

                if (isset($formula->join)) {
                    /* @var $joinContainerXml Varien_Simplexml_Element */
                    $joinContainerXml = $formula->join;
                    foreach ($joinContainerXml->children() as $joinAlias => $definition) {
                        /* @var $joinClosure Mana_Db_Model_Formula_Closure_Join */
                        $joinClosure = Mage::getModel('mana_db/formula_closure_join', compact('context', 'definition'));
                        $alias
                            ->child($formulaHelper->createAlias($joinAlias))
                            ->each($joinClosure);

                    }
                }
                return $context->getHelper()->expr()
                    ->setFieldExpr($alias->expr($context, (string)$formula->expr))
                    ->setFieldName($field)
                    ->setType((string)$fieldsXml->$field->type);
            }
            else {
                return false;
            }
        }
    }

    public function getPrimaryKey($entity) {
        /* @var $resource Mana_Db_Resource_Formula */
        $resource = Mage::getResourceSingleton('mana_db/formula');

        foreach ($resource->getTableFields($entity) as $field) {
            if (!empty($field['PRIMARY'])) {
                return $field['COLUMN_NAME'];
            }
        }

        return false;
    }

}