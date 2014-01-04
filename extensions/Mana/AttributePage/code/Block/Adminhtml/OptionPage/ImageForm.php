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
class Mana_AttributePage_Block_Adminhtml_OptionPage_ImageForm extends Mana_AttributePage_Block_Adminhtml_OptionPage_AbstractForm
{
    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm() {
        $form = new Varien_Data_Form(array(
            'id' => 'mf_image',
            'html_id_prefix' => 'mf_image_',
            'field_container_id_prefix' => 'mf_image_tr_',
            'use_container' => true,
            'method' => 'post',
            'action' => $this->getUrl('*/*/save', array('_current' => true)),
            'field_name_suffix' => 'fields',
            'flat_model' => $this->getFlatModel(),
            'edit_model' => $this->getEditModel(),
        ));

        $fieldset = $this->addFieldset($form, 'mfs_featured_image', array(
            'title' => $this->__('Featured Block Image'),
            'legend' => $this->__('Featured Block Image'),
        ));

        $this->addField($fieldset, 'featured_image', 'image', array(
            'label' => $this->__('Image'),
            'title' => $this->__('Image'),
            'name' => 'featured_image',
            'required' => false,

            'default_bit_no' => Mana_AttributePage_Model_OptionPage_Abstract::DM_FEATURED_IMAGE,
            'default_label' => $this->__('Same As Base Image'),
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'featured_image_width', 'text', array(
            'label' => $this->__('Width'),
            'title' => $this->__('Width'),
            'name' => 'featured_image_width',
            'required' => false,

            'default_bit_no' => Mana_AttributePage_Model_OptionPage_Abstract::DM_FEATURED_IMAGE_WIDTH,
            'default_label' => $this->__('Same For All Option Pages'),
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'featured_image_height', 'text', array(
            'label' => $this->__('Height'),
            'title' => $this->__('Height'),
            'name' => 'featured_image_height',
            'required' => false,

            'default_bit_no' => Mana_AttributePage_Model_OptionPage_Abstract::DM_FEATURED_IMAGE_HEIGHT,
            'default_label' => $this->__('Same For All Option Pages'),
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $fieldset = $this->addFieldset($form, 'mfs_product_image', array(
            'title' => $this->__('Product Page Image'),
            'legend' => $this->__('Product Page Image'),
        ));

        $this->addField($fieldset, 'show_product_image', 'select', array(
            'label' => $this->__('Show'),
            'title' => $this->__('Show'),
            'options' => $this->getYesNoSourceModel()->getOptionArray(),
            'name' => 'show_product_image',
            'required' => true,

            'default_bit_no' => Mana_AttributePage_Model_OptionPage_Abstract::DM_SHOW_PRODUCT_IMAGE,
            'default_label' => $this->__('Same For All Option Pages'),
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'product_image', 'image', array(
            'label' => $this->__('Image'),
            'title' => $this->__('Image'),
            'name' => 'product_image',
            'required' => false,

            'default_bit_no' => Mana_AttributePage_Model_OptionPage_Abstract::DM_PRODUCT_IMAGE,
            'default_label' => $this->__('Same As Base Image'),
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'product_image_width', 'text', array(
            'label' => $this->__('Width'),
            'title' => $this->__('Width'),
            'name' => 'product_image_width',
            'required' => false,

            'default_bit_no' => Mana_AttributePage_Model_OptionPage_Abstract::DM_PRODUCT_IMAGE_WIDTH,
            'default_label' => $this->__('Same For All Option Pages'),
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'product_image_height', 'text', array(
            'label' => $this->__('Height'),
            'title' => $this->__('Height'),
            'name' => 'product_image_height',
            'required' => false,

            'default_bit_no' => Mana_AttributePage_Model_OptionPage_Abstract::DM_PRODUCT_IMAGE_HEIGHT,
            'default_label' => $this->__('Same For All Option Pages'),
            'default_store_label' => $this->__('Same For All Stores'),
        ));
/*
        $fieldset = $this->addFieldset($form, 'mfs_sidebar_image', array(
            'title' => $this->__('Sidebar Image'),
            'legend' => $this->__('Sidebar Image'),
        ));

        $this->addField($fieldset, 'sidebar_image', 'image', array(
            'label' => $this->__('Image'),
            'title' => $this->__('Image'),
            'name' => 'sidebar_image',
            'required' => false,

            'default_bit_no' => Mana_AttributePage_Model_OptionPage_Abstract::DM_SIDEBAR_IMAGE,
            'default_label' => $this->__('Same As Base Image'),
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'sidebar_image_width', 'text', array(
            'label' => $this->__('Width'),
            'title' => $this->__('Width'),
            'name' => 'sidebar_image_width',
            'required' => false,

            'default_bit_no' => Mana_AttributePage_Model_OptionPage_Abstract::DM_SIDEBAR_IMAGE_WIDTH,
            'default_label' => $this->__('Same For All Option Pages'),
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'sidebar_image_height', 'text', array(
            'label' => $this->__('Height'),
            'title' => $this->__('Height'),
            'name' => 'sidebar_image_height',
            'required' => false,

            'default_bit_no' => Mana_AttributePage_Model_OptionPage_Abstract::DM_SIDEBAR_IMAGE_HEIGHT,
            'default_label' => $this->__('Same For All Option Pages'),
            'default_store_label' => $this->__('Same For All Stores'),
        ));
*/
        $this->setForm($form);
        return parent::_prepareForm();
    }
}