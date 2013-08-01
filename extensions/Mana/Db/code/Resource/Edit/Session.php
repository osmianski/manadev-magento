<?php

/* BASED ON SNIPPET: Resources/Single model DB operations */
/**
 * This resource model handles DB operations with a single model of type Mana_Db_Model_Edit_Session. All 
 * database specific code for Mana_Db_Model_Edit_Session should go here.
 * @author Mana Team
 */
class Mana_Db_Resource_Edit_Session extends Mage_Core_Model_Mysql4_Abstract {//Mage_Core_Model_Resource_Abstract
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
    
    public function begin() {
    	$this->beginTransaction();
    	try {
    		$db = $this->_getWriteAdapter();
    		$table = Mage::getSingleton('core/resource')->getTableName('mana_db/edit_session');
    		
    		$db->delete($table, "id <> 0 AND (CURRENT_TIMESTAMP - created_at > 24*60*60)");
    		$db->insert($table, array());
			$result = $db->lastInsertId($table);
    		$this->commit();
    		return $result;
    	}
    	catch (Exception $e) {
    		$this->rollBack();
    		throw $e;
    	}
    }

    public function isExpired($editSessionId) {
        $db = $this->_getReadAdapter();
        $table = Mage::getSingleton('core/resource')->getTableName('mana_db/edit_session');

        $select = $db->select()
            ->from($table, 'id')
            ->where("id = ?", $editSessionId);

        return $db->fetchOne($select) ? false : true;
    }
}