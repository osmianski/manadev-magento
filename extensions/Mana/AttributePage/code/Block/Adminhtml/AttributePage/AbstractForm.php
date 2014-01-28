<?php
/** 
 * @category    Mana
 * @package     Mana_AttributePage
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
abstract class Mana_AttributePage_Block_Adminhtml_AttributePage_AbstractForm extends Mana_Admin_Block_V3_Form {
    #region Dependencies
    /**
     * @return Mana_AttributePage_Model_AttributePage_Abstract
     */
    public function getFlatModel() {
        return Mage::registry('m_flat_model');
    }

    /**
     * @return Mana_AttributePage_Model_AttributePage_Abstract
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
     * @return Mana_AttributePage_Model_Source_Attribute
     */
    public function getAttributeSourceModel() {
        return Mage::getSingleton('mana_attributepage/source_attribute');
    }

    /**
     * @return Mana_Core_Model_Source_Status
     */
    public function getStatusSourceModel() {
        return Mage::getSingleton('mana_core/source_status');
    }

    /**
     * @return Mana_AttributePage_Model_Source_DescriptionPosition
     */
    public function getDescriptionPositionSourceModel() {
        return Mage::getSingleton('mana_attributepage/source_descriptionPosition');
    }

    /**
     * @return Mana_AttributePage_Model_Source_SortBy
     */
    public function getSortBySourceModel() {
        return Mage::getSingleton('mana_attributepage/source_sortBy');
    }

    #endregion
}