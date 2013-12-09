<?php
/** 
 * @category    Mana
 * @package     Mana_Admin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Admin_Block_V3_Field extends Mana_Admin_Block_V2_Field {
    /**
     * Returns true if field can use default value (calculated in model indexer) and false otherwise
     * @return bool
     */
    public function getDisplayUseDefault()
    {
	    return !$this->getElement()->getData('hide_use_default') && ($this->adminHelper()->isGlobal()
	        ? $this->getElement()->hasData('default_bit_no') && $this->getElement()->getData('default_label')
	        : $this->getElement()->hasData('default_bit_no') && $this->getElement()->getData('default_store_label'));
    }

    /**
     * Returns label text for "Use Default checkbox"
     * @return string
     */
    public function getDefaultLabel() {
        return $this->adminHelper()->isGlobal()
	        ? $this->getElement()->getData('default_label')
	        : $this->getElement()->getData('default_store_label');
    }

    /**
     * Returns true if field uses default value (calculated in model indexer), returns false if field
     * contains custom (overridden) value
     * @return bool
     */
    public function getUsedDefault() {
        $bitNo = $this->getElement()->getData('default_bit_no');
        $mask = $this->getEditModel()->getData("default_mask{$this->dbHelper()->getMaskIndex($bitNo)}");
        $bit = $this->dbHelper()->getMask($bitNo);

	    return $this->getDisplayUseDefault() && ($mask & $bit) != $bit;
	}

	#region Dependencies

    /**
     * @return Mana_Core_Helper_Db
     */
    public function dbHelper() {
	    return Mage::helper('mana_core/db');
	}
	#endregion
}