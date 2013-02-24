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
class Mana_AttributePage_Block_Adminhtml_New_Form extends Mana_Admin_Block_Crud_Card_Form {
    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm() {
        // form - collection of fieldsets
        $form = new Varien_Data_Form(array(
            'id' => 'mf_new',
            'html_id_prefix' => 'mf_new_',
            'use_container' => true,
            'method' => 'post',
            'action' => $this->getUrl('*/*/newPost', array('_current' => true)),
            'field_name_suffix' => 'fields',
            'model' => Mage::registry('m_crud_model'),
        ));
        /** @noinspection PhpUndefinedMethodInspection */
        Mage::helper('mana_core/js')->options('edit-form', array('subforms' => array('#mf_general' => '#mf_general')));

        // fieldset - collection of fields
        /** @noinspection PhpParamsInspection */
        $fieldset = $form->addFieldset('mfs_general', array(
            'title' => $this->__('Attribute'),
            'legend' => $this->__('Attribute'),
        ));
        $fieldset->setRenderer($this->getLayout()->getBlockSingleton('mana_admin/crud_card_fieldset'));

        /** @noinspection PhpUndefinedMethodInspection */
        $field = $fieldset->addField('name', 'text', array(
            'label' => $this->__('Name'),
            'name' => 'name',
            'required' => true,
        ));
        $field->setRenderer($this->getLayout()->getBlockSingleton('mana_admin/crud_card_field'));

        $this->setForm($form);
        return parent::_prepareForm();
    }
}