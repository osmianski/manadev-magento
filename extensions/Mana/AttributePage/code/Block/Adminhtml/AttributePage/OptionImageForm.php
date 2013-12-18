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
class Mana_AttributePage_Block_Adminhtml_AttributePage_OptionImageForm  extends Mana_AttributePage_Block_Adminhtml_AttributePage_AbstractForm
{
    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm() {
        $form = new Varien_Data_Form(array(
            'id' => 'mf_option_image',
            'html_id_prefix' => 'mf_option_image_',
            'field_container_id_prefix' => 'mf_option_image_tr_',
            'use_container' => true,
            'method' => 'post',
            'action' => $this->getUrl('*/*/save', array('_current' => true)),
            'field_name_suffix' => 'fields',
            'flat_model' => $this->getFlatModel(),
            'edit_model' => $this->getEditModel(),
        ));

        $fieldset = $this->addFieldset($form, 'mfs_option_featured_image', array(
            'title' => $this->__('Featured Block Image'),
            'legend' => $this->__('Featured Block Image'),
        ));

        $this->addField($fieldset, 'option_page_featured_image_width', 'text', array(
            'label' => $this->__('Width'),
            'title' => $this->__('Width'),
            'name' => 'option_page_featured_image_width',
            'required' => false,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_FEATURED_IMAGE_WIDTH,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'option_page_featured_image_height', 'text', array(
            'label' => $this->__('Height'),
            'title' => $this->__('Height'),
            'name' => 'option_page_featured_image_height',
            'required' => false,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_FEATURED_IMAGE_HEIGHT,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $fieldset = $this->addFieldset($form, 'mfs_option_product_image', array(
            'title' => $this->__('Product Page Image'),
            'legend' => $this->__('Product Page Image'),
        ));

        $this->addField($fieldset, 'option_page_product_image_width', 'text', array(
            'label' => $this->__('Width'),
            'title' => $this->__('Width'),
            'name' => 'option_page_product_image_width',
            'required' => false,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_PRODUCT_IMAGE_WIDTH,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'option_page_product_image_height', 'text', array(
            'label' => $this->__('Height'),
            'title' => $this->__('Height'),
            'name' => 'option_page_product_image_height',
            'required' => false,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_PRODUCT_IMAGE_HEIGHT,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $fieldset = $this->addFieldset($form, 'mfs_option_sidebar_image', array(
            'title' => $this->__('Sidebar Image'),
            'legend' => $this->__('Sidebar Image'),
        ));

        $this->addField($fieldset, 'option_page_sidebar_image_width', 'text', array(
            'label' => $this->__('Width'),
            'title' => $this->__('Width'),
            'name' => 'option_page_sidebar_image_width',
            'required' => false,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_SIDEBAR_IMAGE_WIDTH,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'option_page_sidebar_image_height', 'text', array(
            'label' => $this->__('Height'),
            'title' => $this->__('Height'),
            'name' => 'option_page_sidebar_image_height',
            'required' => false,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_SIDEBAR_IMAGE_HEIGHT,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->setForm($form);
        return parent::_prepareForm();
    }
}