<?php
/** 
 * @category    Mana
 * @package     Mana_Content
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Content_Block_Adminhtml_Book_AbstractForm extends Mana_Admin_Block_V3_Form {
    #region Dependencies
    /**
     * @return Mana_Content_Model_Page_Abstract
     */
    public function getFlatModel() {
        return Mage::registry('m_flat_model');
    }

    /**
     * @return Mana_Content_Model_Page_Abstract
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

    #endregion
}