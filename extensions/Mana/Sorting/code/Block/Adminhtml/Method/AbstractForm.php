<?php
/** 
 * @category    Mana
 * @package     Mana_Sorting
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Sorting_Block_Adminhtml_Method_AbstractForm extends Mana_Admin_Block_V3_Form {
    #region Dependencies
    /**
     * @return Mana_Sorting_Model_Method_Abstract
     */
    public function getFlatModel() {
        return Mage::registry('m_flat_model');
    }

    /**
     * @return Mana_Sorting_Model_Method_Abstract
     */
    public function getEditModel() {
        return Mage::registry('m_edit_model');
    }

    /**
     * @return Mana_Core_Model_Source_Layout
     */
    public function getPageLayoutSourceModel() {
        return Mage::getSingleton('mana_core/source_layout');
    }

    /**
     * @return Mana_Core_Model_Source_Design
     */
    public function getDesignSourceModel() {
        return Mage::getSingleton('mana_core/source_design');
    }

    /**
     * @return Mana_Core_Model_Source_Status
     */
    public function getStatusSourceModel() {
        return Mage::getSingleton('mana_core/source_status');
    }
    /**
     * @return Mana_Sorting_Model_Method_Abstract
     */
    public function getGlobalFlatModel() {
        return Mage::registry('m_global_flat_model');
    }

    /**
     * @return Mana_Sorting_Model_Method_Abstract
     */
    public function getGlobalEditModel() {
        return Mage::registry('m_global_edit_model');
    }

    /**
     * @return Mana_Core_Helper_Db
     */
    public function coreDbHelper() {
        return Mage::helper('mana_core/db');
    }

    /**
     * @return Mana_Sorting_Model_Source_Attribute
     */
    public function getAttributeSourceModel() {
        return Mage::getSingleton('mana_sorting/source_attribute');
    }

    #endregion
}