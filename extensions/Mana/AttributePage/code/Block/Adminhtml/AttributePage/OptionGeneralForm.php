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
class Mana_AttributePage_Block_Adminhtml_AttributePage_OptionGeneralForm  extends Mana_AttributePage_Block_Adminhtml_AttributePage_AbstractForm
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
            'id' => 'mf_option_general',
            'html_id_prefix' => 'mf_option_general_',
            'field_container_id_prefix' => 'mf_option_general_tr_',
            'use_container' => true,
            'method' => 'post',
            'action' => $this->getUrl('*/*/save', array('_current' => true)),
            'field_name_suffix' => 'fields',
            'flat_model' => $this->getFlatModel(),
            'edit_model' => $this->getEditModel(),
        ));

        $fieldset = $this->addFieldset($form, 'mfs_option_content', array(
            'title' => $this->__('Content'),
            'legend' => $this->__('Content'),
        ));

        $this->addField($fieldset, 'option_page_description_position', 'select', array(
            'label' => $this->__('Description Position'),
            'title' => $this->__('Description Position'),
            'options' => $this->getDescriptionPositionSourceModel()->getOptionArray(),
            'name' => 'option_page_description_position',
            'required' => true,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_DESCRIPTION_POSITION,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $fieldset = $this->addFieldset($form, 'mfs_option_base_image', array(
            'title' => $this->__('Base Image'),
            'legend' => $this->__('Base Image'),
        ));

        $this->addField($fieldset, 'option_page_image', 'image', array(
            'label' => $this->__('Image'),
            'title' => $this->__('Image'),
            'name' => 'option_page_image',
            'required' => false,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_IMAGE,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'option_page_image_width', 'text', array(
            'label' => $this->__('Width'),
            'title' => $this->__('Width'),
            'name' => 'option_page_image_width',
            'required' => false,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_IMAGE_WIDTH,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'option_page_image_height', 'text', array(
            'label' => $this->__('Height'),
            'title' => $this->__('Height'),
            'name' => 'option_page_image_height',
            'required' => false,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_IMAGE_HEIGHT,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $fieldset = $this->addFieldset($form, 'mfs_option_other', array(
            'title' => $this->__('Other Settings'),
            'legend' => $this->__('Other Settings'),
        ));

        $this->addField($fieldset, 'option_page_is_active', 'select', array(
            'label' => $this->__('Status'),
            'title' => $this->__('Status'),
            'options' => $this->getStatusSourceModel()->getOptionArray(),
            'name' => 'option_page_is_active',
            'required' => true,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_IS_ACTIVE,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'option_page_include_in_menu', 'select', array(
            'label' => $this->__('Include In Menu'),
            'title' => $this->__('Include In Menu'),
            'options' => $this->getYesNoSourceModel()->getOptionArray(),
            'name' => 'option_page_include_in_menu',
            'required' => true,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_INCLUDE_IN_MENU,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'option_page_is_featured', 'select', array(
            'label' => $this->__('Featured'),
            'title' => $this->__('Featured'),
            'options' => $this->getYesNoSourceModel()->getOptionArray(),
            'name' => 'option_page_is_featured',
            'required' => true,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_IS_FEATURED,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->setForm($form);
        return parent::_prepareForm();
    }
}