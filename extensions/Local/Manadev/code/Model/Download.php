<?php
/**
 * @category    Mana
 * @package     Local_Manadev
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/* BASED ON SNIPPET: Models/DB-backed model */
/**
 * This data structure holds stats of a single download fact - who, when, from where and what 
 * @author Mana Team
 */
class Local_Manadev_Model_Download extends Mage_Core_Model_Abstract {
    /**
     * Invoked during model creation process, this method associates this model with resource and resource
     * collection classes
     */
	protected function _construct()	{
		$this->_init(strtolower('Local_Manadev/Download'));
	}
}
