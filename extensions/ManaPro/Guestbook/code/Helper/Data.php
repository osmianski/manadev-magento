<?php
/** 
 * @category    Mana
 * @package     ManaPro_Guestbook
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * Generic helper functions for ManaPro_Guestbook module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class ManaPro_Guestbook_Helper_Data extends Mage_Core_Helper_Abstract {
    protected $_allFields = array('email', 'url', 'name', 'text', 'country', 'region');
	protected $_gridColumns;
	public function getGridColumns() {
	    if (!$this->_gridColumns) {
	        $this->_gridColumns = array();
	        foreach ($this->_allFields as $field) {
	            if (Mage::getStoreConfigFlag("manapro_guestbook/$field/is_enabled") && Mage::getStoreConfigFlag("manapro_guestbook/$field/in_grid")) {
	                $this->_gridColumns[$field] = Mage::getStoreConfig("manapro_guestbook/$field/position");
	            }
	        }
	        asort($this->_gridColumns);
	        $this->_gridColumns = array_keys($this->_gridColumns);
	    }
	    return $this->_gridColumns;
	}
	protected $_visibleFields;
    public function getVisibleFields() {
        if (!$this->_visibleFields) {
            $this->_visibleFields = array();
            foreach ($this->_allFields as $field) {
                //if (Mage::getStoreConfigFlag("manapro_guestbook/$field/is_enabled")) {
                    $this->_visibleFields[$field] = Mage::getStoreConfig("manapro_guestbook/$field/position");
                //}
            }
            asort($this->_visibleFields);
            $this->_visibleFields = array_keys($this->_visibleFields);
        }
        return $this->_visibleFields;
    }
    protected $_requiredFields;
    public function getRequiredFields() {
        if (!$this->_requiredFields) {
            $this->_requiredFields = array();
            foreach ($this->_allFields as $field) {
                if (Mage::getStoreConfigFlag("manapro_guestbook/$field/is_enabled") && Mage::getStoreConfigFlag("manapro_guestbook/$field/is_required")) {
                    $this->_requiredFields[$field] = Mage::getStoreConfig("manapro_guestbook/$field/position");
                }
            }
            asort($this->_requiredFields);
            $this->_requiredFields = array_keys($this->_requiredFields);
        }
        return $this->_requiredFields;
    }
}