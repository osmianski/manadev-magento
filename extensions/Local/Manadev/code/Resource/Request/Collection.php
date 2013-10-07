<?php
/**
 * @category    Mana
 * @package     Local_Manadev
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/* BASED ON SNIPPET: Resources/DB operations with model collections */
/**
 * This resource model handles DB operations with a collection of models of type Local_Manadev_Model_Request. All 
 * database specific code for operating collection of Local_Manadev_Model_Request should go here.
 * @author Mana Team
 */
class Local_Manadev_Resource_Request_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Invoked during resource collection model creation process, this method associates this 
     * resource collection model with model class and with resource model class
     */
    protected function _construct()
    {
        $this->_init(strtolower('Local_Manadev/Request'));
    }

}
