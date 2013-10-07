<?php
/**
 * @category    Mana
 * @package     Local_Manadev
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/* BASED ON SNIPPET: Resources/Single model DB operations */
/**
 * This resource model handles DB operations with a single model of type Local_Manadev_Model_Download. All 
 * database specific code for Local_Manadev_Model_Download should go here.
 * @author Mana Team
 */
class Local_Manadev_Resource_Download extends Mage_Core_Model_Mysql4_Abstract {
    /**
     * Invoked during resource model creation process, this method associates this resource model with model class
     * and with DB table name
     */
	protected function _construct() {
        $this->_init(strtolower('Local_Manadev/Download'), 'id');
        $this->_isPkAutoIncrement = false;
    }   
}