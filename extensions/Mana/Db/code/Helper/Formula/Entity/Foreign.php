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
class Mana_Db_Helper_Formula_Entity_Foreign extends Mana_Db_Helper_Formula_Entity {
    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Entity $entity
     */
    public function select($context, $entity) {
        switch ($context->getMode()) {
            default:
                if (!$context->hasAlias($entity->getAlias()->asString(0))) {
                    /* @var $resource Mana_Db_Resource_Formula */
                    $resource = Mage::getResourceSingleton('mana_db/formula');

                    /* @var $dbHelper Mana_Db_Helper_Data */
                    $dbHelper = Mage::helper('mana_db');

                    /* @var $joinClosure Mana_Db_Model_Formula_Closure_ForeignJoinEnd */
                    $joinClosure = Mage::getModel('mana_db/formula_closure_foreignJoinEnd', compact('context', 'entity'));
                    $context->getAlias()->each($joinClosure);
                }

                $context
                    ->setEntity($entity->getEntity())
                    ->setProcessor($entity->getProcessor())
                    ->setAlias($entity->getAlias())
                    ->setEntityHelper($this);
                break;
        }

    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Node_Field $formula
     * @param Mana_Db_Model_Formula_Expr $expr
     */
    public function selectField($context, $formula, $expr) {
        $expr->setExpr($expr->getFieldExpr());
    }
}