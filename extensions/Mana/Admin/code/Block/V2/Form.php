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
class Mana_Admin_Block_V2_Form extends Mage_Adminhtml_Block_Widget_Form {
    protected function _construct() {
        parent::_construct();
        $this->setTemplate('mana/admin/v2/form.phtml');
    }

    protected function _prepareLayout() {
        parent::_prepareLayout();

        // create client-side block
        $this->_prepareClientSideBlock();

        return $this;
    }

    protected function _prepareClientSideBlock() {
        $block = array(
            'type' => 'Mana/Admin/Form',
            'self_contained' => true
        );

        $this->setData('m_client_side_block', $block);

        return $this;
    }

    protected function _prepareForm() {
        Mage::dispatchEvent('m_crud_form', array('form' => $this));

        return parent::_prepareForm();
    }

    protected function _initFormValues() {
        $this->getForm()->setValues($this->getForm()->getData('flat_model')->getData());

        return parent::_initFormValues();
    }

    /**
     * @param Varien_Data_Form $form
     * @param string $id
     * @param array $options
     * @return Varien_Data_Form_Element_Fieldset
     */
    public function addFieldset($form, $id, $options) {
        if (isset($options['renderer'])) {
            $renderer = $this->getLayout()->getBlockSingleton($options['renderer']);
            unset($options['renderer']);
        }
        else {
            $renderer = $this->getFieldsetRenderer();
        }

        /** @noinspection PhpParamsInspection */
        $fieldset = $form->addFieldset($id, $options);


        $fieldset->setRenderer($renderer);
        $fieldset->addType('select_text', 'Mana_Admin_Block_V2_Form_Field_SelectText');
        $fieldset->addType('wysiwyg', 'Mana_Admin_Block_V2_Form_Field_Wysiwyg');
        $fieldset->addType('image', 'Mana_Admin_Block_V2_Form_Field_Image');

        return $fieldset;
    }

    /**
     * @param Varien_Data_Form_Element_Fieldset $fieldset
     * @param string $id
     * @param string $type
     * @param array $options
     * @return Varien_Data_Form_Element_Abstract
     */
    public function addField($fieldset, $id, $type, $options) {
        if (isset($options['renderer'])) {
            $renderer = $this->getLayout()->getBlockSingleton($options['renderer']);
            unset($options['renderer']);
        }
        else {
            $renderer = $this->getFieldRenderer();
        }
        /** @noinspection PhpParamsInspection */
        $field = $fieldset->addField($id, $type, $options);
        if (isset($options['values'])) {
            $field->setValues($options['values']);
        }
        $field->setRenderer($renderer);

        return $field;
    }
    #region Dependencies
    /**
     * @return Mana_Admin_Helper_Data
     */
    public function adminHelper() {
        return Mage::helper('mana_admin');
    }

    /**
     * @return Mana_Core_Helper_Data
     */
    public function coreHelper() {
        return Mage::helper('mana_core');
    }

    /**
     * @return Mana_Db_Helper_Data
     */
    public function dbHelper() {
        return Mage::helper('mana_db');
    }

    /**
     * @return Mana_Core_Helper_Js
     */
    public function jsHelper() {
        return Mage::helper('mana_core/js');
    }

    /**
     * @return Mana_Admin_Block_V2_Fieldset
     */
    public function getFieldsetRenderer() {
        return $this->getLayout()->getBlockSingleton('mana_admin/v2_fieldset');
    }

    /**
     * @return Mana_Admin_Block_V2_Field
     */
    public function getFieldRenderer() {
        return $this->getLayout()->getBlockSingleton('mana_admin/v2_field');
    }

    /**
     * @return Mana_Core_Model_Source_Yesno
     */
    public function getYesNoSourceModel() {
        return Mage::getSingleton('mana_core/source_yesno');
    }
    #endregion
}