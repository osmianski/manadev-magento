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
abstract class Mana_AttributePage_Block_Adminhtml_OptionPage_AbstractForm extends Mana_Admin_Block_V3_Form {
    #region Dependencies
    /**
     * @return Mana_AttributePage_Model_OptionPage_Abstract
     */
    public function getFlatModel() {
        return Mage::registry('m_flat_model');
    }

    /**
     * @return Mana_AttributePage_Model_OptionPage_Abstract
     */
    public function getEditModel() {
        return Mage::registry('m_edit_model');
    }

    /**
     * @return Mana_AttributePage_Model_AttributePage_Abstract
     */
    public function getAttributePage() {
        return Mage::registry('m_attribute_page');
    }

    /**
     * @return Mana_AttributePage_Model_AttributePage_Abstract
     */
    public function getGlobalAttributePage() {
        return Mage::registry('m_global_attribute_page');
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
     * @return Mana_AttributePage_Model_Source_Status
     */
    public function getStatusSourceModel() {
        return Mage::getSingleton('mana_attributepage/source_status');
    }

    /**
     * @return Mana_AttributePage_Model_Source_SortBy
     */
    public function getSortBySourceModel() {
        return Mage::getSingleton('mana_attributepage/source_sortBy');
    }
    /**
     * @return Mana_AttributePage_Model_Source_Attribute
     */
    public function getAttributeSourceModel() {
        return Mage::getSingleton('mana_attributepage/source_attribute');
    }

    /**
     * @param $attributeId
     * @return Mana_AttributePage_Model_Source_Option
     */
    public function getOptionSourceModel($attributeId) {
        return Mage::getModel('mana_attributepage/source_option')->setAttributeId($attributeId);
    }

    /**
     * @return Mana_AttributePage_Model_Source_DescriptionPosition
     */
    public function getDescriptionPositionSourceModel() {
        return Mage::getSingleton('mana_attributepage/source_descriptionPosition');
    }

    #endregion
}