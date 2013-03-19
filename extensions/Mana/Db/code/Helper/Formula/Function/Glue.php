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
        if (count($args) < 2 || count($args) > 3) {
            throw new Mana_Db_Exception_Formula($this->__("Function '%s' expects 2 or 3 parameters", $this->getName()));
        }
        if (!$args[0]->getIsAggregate()) {
            throw new Mana_Db_Exception_Formula($this->__("You can't use aggregate function on non-aggregate fields"));
        }

        /* @var $res Mage_Core_Model_Resource */
        $res = Mage::getSingleton('core/resource');

        /* @var $db Varien_Db_Adapter_Pdo_Mysql */
        $db = $res->getConnection('read');

        $helper = $context->getHelper();

        $sql = $db->select();
        throw new Exception('Not implemented'); // prepare subselect
        return $helper->expr()->setExpr("($sql)")->setType('varchar(255)');
    }
}