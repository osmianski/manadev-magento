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
class Mana_AttributePage_Block_Adminhtml_AttributePage_GeneralForm extends Mana_AttributePage_Block_Adminhtml_AttributePage_AbstractForm
{
    protected function _prepareLayout() {
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setData('can_load_tiny_mce', true);
        }
    }

    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm() {
        $form = new Varien_Data_Form(array(
            'id' => 'mf_general',
            'html_id_prefix' => 'mf_general_',
            'field_container_id_prefix' => 'mf_general_tr_',
            'use_container' => true,
            'method' => 'post',
            'action' => $this->getUrl('*/*/save', array('_current' => true)),
            'field_name_suffix' => 'fields',
            'flat_model' => $this->getFlatModel(),
            'edit_model' => $this->getEditModel(),
        ));

        $fieldset = $this->addFieldset($form, 'mfs_content', array(
            'title' => $this->__('Content'),
            'legend' => $this->__('Content'),
        ));

        $this->addField($fieldset, 'title', 'text', array(
            'label' => $this->__('Title'),
            'title' => $this->__('Title'),
            'name' => 'title',
            'required' => true,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_TITLE,
            'default_label' => $this->__('Use Attribute Labels'),
            'default_store_label' => $this->__('Use Attribute Labels'),
        ));

        $this->addField($fieldset, 'description', 'editor', array(
            'name'      => 'description',
            'label'     => Mage::helper('cms')->__('Description'),
            'title'     => Mage::helper('cms')->__('Description'),
            //'style'     => 'height:36em',
            'required'  => true,
            'config'    => Mage::getSingleton('cms/wysiwyg_config')->getConfig(),

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_DESCRIPTION,
            'default_label' => $this->__('Use Title'),
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $fieldset = $this->addFieldset($form, 'mfs_other', array(
            'title' => $this->__('Other Settings'),
            'legend' => $this->__('Other Settings'),
        ));

        $this->addField($fieldset, 'is_active', 'select', array(
            'label' => $this->__('Status'),
            'title' => $this->__('Status'),
            'options' => $this->getStatusSourceModel()->getOptionArray(),
            'name' => 'is_active',
            'required' => true,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_IS_ACTIVE,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'include_in_menu', 'select', array(
            'label' => $this->__('Include In Menu'),
            'title' => $this->__('Include In Menu'),
            'options' => $this->getYesNoSourceModel()->getOptionArray(),
            'name' => 'include_in_menu',
            'required' => true,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_INCLUDE_IN_MENU,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->setForm($form);
        return parent::_prepareForm();
    }
}