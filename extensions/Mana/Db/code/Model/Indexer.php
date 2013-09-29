<?php
/**
 * @category    Mana
 * @package     Mana_Db
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Entry points for cron and index processes
 * @author Mana Team
 *
 */
class Mana_Db_Model_Indexer extends Mage_Index_Model_Indexer_Abstract {
	// INDEXING ITSELF
	
    protected function _construct()
    {
        $this->_init('mana_db/replicate');
    }
    public function getName()
    {
        return Mage::helper('mana_db')->__('Default Values (MANAdev)');
    }
    public function getDescription()
    {
        return Mage::helper('mana_db')->__('Propagate default values throughout the system');
    }
    protected function _registerEvent(Mage_Index_Model_Event $event)
    {
    }
    protected function _processEvent(Mage_Index_Model_Event $event)
    {
    }
	public function reindexAll() {
		Mage::helper('mana_db')->replicate();
	}
    
    public function runCronjob()
    {
        $this->reindexAll();
    }
}