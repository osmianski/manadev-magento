<?php
/**
 * @category    Mana
 * @package     Mana_Admin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Renders one fieldset field including label, field itself and 'use default checkbox'
 * @author Mana Team
 *
 */
class Mana_Admin_Block_V2_Field extends Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset_Element {
    protected function _construct()
    {
        $this->setTemplate('mana/admin/v2/field.phtml');
    }

    /**
     * Returns true if field uses default value (calculated in model indexer), returns false if field
     * contains custom (overridden) value
     * @return bool
     */
    public function getUsedDefault() {
	    return $this->getEditModel()->isUsingDefaultData($this->getFieldName());
	}
    public function checkFieldDisable()
    {
        if ($this->getDisplayUseDefault() && $this->getUsedDefault()) {
            $this->getElement()->setData('disabled', true);
        }
        return $this;
    }

    /**
     * Returns true if field can use default value (calculated in model indexer) and false otherwise
     * @return bool
     */
    public function getDisplayUseDefault()
    {
        return !$this->getElement()->getData('hide_use_default') && $this->adminHelper()->getDefaultFormula($this->getFlatModel(), $this->getFieldName());
    }

    /**
     * Returns true if "Use Default"  checkbox is currently enabled
     * @return bool
     */
    public function getUseDefaultEnabled()
    {
    	return !$this->getElement()->getData('is_default_disabled');
    }

    /**
     * Returns label text for "Use Default checkbox"
     * @return string
     */
    public function getDefaultLabel() {
        return $this->adminHelper()->getDefaultLabel($this->getFlatModel(), $this->getFieldName());
    }
    /**
     * @return Varien_Data_Form_Element_Abstract
     */
    public function getElement() {
        return $this->_element;
    }

    /**
     * @return Varien_Data_Form
     */
    public function getForm() {
        return $this->getElement()->getForm();
    }

    /**
     * @return Mana_Db_Model_Entity
     */
    public function getEditModel() {
        return $this->getForm()->getData('edit_model');
    }

    /**
     * @return Mana_Db_Model_Entity
     */
    public function getFlatModel() {
        return $this->getForm()->getData('flat_model');
    }

    public function getFieldName() {
        return $this->getElement()->getData('name');
    }
    #region Dependencies

    /**
     * @return Mana_Admin_Helper_Data
     */
    public function adminHelper() {
        return Mage::helper('mana_admin');
    }

    #endregion
}