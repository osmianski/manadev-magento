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
class Mana_Db_Helper_Formula_Function_Glue extends Mana_Db_Helper_Formula_Function {
    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_TypedExpr[] $args
     * @throws Mana_Db_Exception_Formula
     * @return Mana_Db_Model_Formula_TypedExpr
     */
    public function select($context, $args) {
        /* @var $formulaHelper Mana_Db_Helper_Formula */
        $formulaHelper = Mage::helper('mana_db/formula');

        if (count($args) < 2 || count($args) > 3) {
            throw new Mana_Db_Exception_Formula($this->__("Function '%s' expects 2 or 3 parameters", $this->getName()));
        }
        if (!$args[0]->getIsAggregate()) {
            throw new Mana_Db_Exception_Formula($this->__("Function '%s' expects 1 parameter to be a field of aggregate entity", $this->getName()));
        }

        /* @var $resource Mana_Db_Resource_Formula */
        $resource = Mage::getResourceSingleton('mana_db/formula');

        $select = $resource->getAggregateSubSelect($context, $args[0]);

        $helper = $context->getHelper();

        throw new Exception('Not implemented'); // prepare subselect
        return $helper->expr()->setExpr("($sql)")->setType('varchar(255)');
    }
}