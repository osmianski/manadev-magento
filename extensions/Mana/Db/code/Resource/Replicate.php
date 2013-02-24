<?php

/* BASED ON SNIPPET: Resources/Single model DB operations */
/**
 * This resource model handles DB operations with a single model of type Mana_Db_Model_Edit_Session. All 
 * database specific code for Mana_Db_Model_Edit_Session should go here.
 * @author Mana Team
 */
class Mana_Db_Resource_Replicate extends Mage_Core_Model_Mysql4_Abstract {//Mage_Core_Model_Resource_Abstract
    /**
     * Resource initialization
     */
    protected function _construct() {
    }

    /**
     * Retrieve connection for read data
     */
    protected function _getReadAdapter() {
    	return Mage::getSingleton('core/resource')->getConnection('read');
    }

    /**
     * Retrieve connection for write data
     * @return Zend_Db_Adapter_Abstract
     */
    protected function _getWriteAdapter() {
    	return Mage::getSingleton('core/resource')->getConnection('write');
    }
}