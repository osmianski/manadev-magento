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

    protected function _beforeToHtml() {
        parent::_beforeToHtml();

        // create client-side block
        $this->_prepareClientSideBlock();

        return $this;

    }

    protected function _prepareClientSideBlock() {
        $element = $this->getElement();
        $type = $element->getType();
        if ($element instanceof Mana_Admin_Block_V2_Form_Field_Wysiwyg) {
            $block = array('type' => 'Mana/Admin/Field/Wysiwyg');
        }
        elseif ($element instanceof Mana_Admin_Block_V2_Form_Field_Image) {
            $block = array('type' => 'Mana/Admin/Field/Image');
        }
        elseif ($element instanceof Mana_Admin_Block_V2_Form_Field_SelectText) {
            $block = array('type' => 'Mana/Admin/Field/SelectText');
        }
        elseif ($element instanceof Varien_Data_Form_Element_Multiselect) {
            $block = array('type' => 'Mana/Admin/Field/MultiSelect');
        }
        elseif ($element instanceof Varien_Data_Form_Element_Date) {
            $block = array('type' => 'Mana/Admin/Field/Date');
        }

        elseif ($type == 'select') {
            $block = array('type' => 'Mana/Admin/Field/Select');
        }
        elseif ($type == 'text') {
            $block = array('type' => 'Mana/Admin/Field/Text');
        }
        elseif ($type == 'hidden') {
            $block = array('type' => 'Mana/Admin/Field/Hidden');
        }
        elseif ($type == 'textarea') {
            $block = array('type' => 'Mana/Admin/Field/TextArea');
        }

        else {
            $block = array('type' => 'Mana/Admin/Field');
        }
        $block['self_contained'] = true;
        if ($element->getData('dirty')) {
            $block['dirty'] = true;
        }
        $this->setData('m_client_side_block', $block);

        return $this;
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

    /**
     * @return Mana_Core_Helper_Js
     */
    public function jsHelper() {
        return Mage::helper('mana_core/js');
    }

    #endregion
}