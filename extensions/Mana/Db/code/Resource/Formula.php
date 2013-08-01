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
class Mana_Db_Resource_Formula extends Mage_Core_Model_Mysql4_Abstract {
    /**
     * Resource initialization
     */
    protected function _construct() {
        $this->_setResource('core');
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Expr $expr
     * @return Varien_Db_Select
     */
    public function getAggregateSubSelect($context, $expr) {
        /* @var $db Varien_Db_Adapter_Pdo_Mysql */
        $db = $this->getReadConnection();

        $select = $db->select();
        throw new Exception('Not implemented');
    }

    public function getTableFields($entity) {
        /* @var $dbHelper Mana_Db_Helper_Data */
        $dbHelper = Mage::helper('mana_db');

        return $this->getReadConnection()->describeTable($this->getTable($dbHelper->getScopedName($entity)));
    }

    /**
     * @return Varien_Db_Select
     */
    public function select() {
        /* @var $db Varien_Db_Adapter_Pdo_Mysql */
        $db = $this->getReadConnection();

        return $db->select();
    }

}