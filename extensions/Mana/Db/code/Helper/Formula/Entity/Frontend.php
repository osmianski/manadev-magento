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
class Mana_Db_Helper_Formula_Entity_Frontend extends Mana_Db_Helper_Formula_Entity {
    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Entity $entity
     */
    public function select($context, $entity) {
        switch ($context->getMode()) {
            default:
                $context
                    ->setMode($this->getName())
                    ->setEntityHelper($this);

                $context
                    ->setEntity($entity->getEntity())
                    ->setProcessor($entity->getProcessor())
                    ->setAlias($entity->getAlias());
                break;
        }
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Node_Field $formula
     * @param Mana_Db_Model_Formula_Expr $expr
     */
    public function selectField($context, $formula, $expr) {
        $expr
            ->setIsFrontend(true)
            ->setExpr("'{{= {$formula->__toString()} }}'")
            ->setType('varchar(255');
   }
}