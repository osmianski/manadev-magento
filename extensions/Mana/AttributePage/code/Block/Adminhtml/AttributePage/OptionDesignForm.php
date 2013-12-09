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
class Mana_AttributePage_Block_Adminhtml_AttributePage_OptionDesignForm extends Mana_AttributePage_Block_Adminhtml_AttributePage_AbstractForm
{
    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm() {
        $form = new Varien_Data_Form(array(
            'id' => 'mf_option_design',
            'html_id_prefix' => 'mf_option_design_',
            'field_container_id_prefix' => 'mf_option_design_tr_',
            'use_container' => true,
            'method' => 'post',
            'action' => $this->getUrl('*/*/save', array('_current' => true)),
            'field_name_suffix' => 'fields',
            'flat_model' => $this->getFlatModel(),
            'edit_model' => $this->getEditModel(),
        ));

        $fieldset = $this->addFieldset($form, 'mfs_option_design', array(
            'title' => $this->__('Design'),
            'legend' => $this->__('Design'),
        ));

        $this->addField($fieldset, 'option_page_page_layout', 'select', array(
            'label' => $this->__('Page Layout'),
            'title' => $this->__('Page Layout'),
            'options' => $this->getPageLayoutSourceModel()->getOptionArray(),
            'name' => 'option_page_page_layout',
            'required' => false,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_PAGE_LAYOUT,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'option_page_layout_xml', 'textarea', array(
            'label' => $this->__('Layout XML'),
            'title' => $this->__('Layout XML'),
            'name' => 'option_page_layout_xml',
            'required' => false,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_LAYOUT_XML,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $fieldset = $this->addFieldset($form, 'mfs_option_product_list', array(
            'title' => $this->__('Product List'),
            'legend' => $this->__('Product List'),
        ));

        $this->addField($fieldset, 'option_page_show_products', 'select', array(
            'label' => $this->__('Show Products'),
            'title' => $this->__('Show Products'),
            'options' => $this->getYesNoSourceModel()->getOptionArray(),
            'name' => 'option_page_show_products',
            'required' => true,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_SHOW_PRODUCTS,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'option_page_available_sort_by', 'multiselect', array(
            'label' => $this->__('Available Sort By'),
            'title' => $this->__('Available Sort By'),
            'values' => $this->getSortBySourceModel()->getAllOptions(),
            'name' => 'option_page_available_sort_by',
            'required' => true,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_AVAILABLE_SORT_BY,
            'default_label' => $this->__('Use System Configuration'),
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'option_page_default_sort_by', 'select', array(
            'label' => $this->__('Default Sort By'),
            'title' => $this->__('Default Sort By'),
            'options' => $this->getSortBySourceModel()->getOptionArray(),
            'name' => 'option_page_available_sort_by',
            'required' => true,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_DEFAULT_SORT_BY,
            'default_label' => $this->__('Use System Configuration'),
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $fieldset = $this->addFieldset($form, 'mfs_option_layered_nav', array(
            'title' => $this->__('Layered Navigation'),
            'legend' => $this->__('Layered Navigation'),
        ));

        $this->addField($fieldset, 'option_page_price_step', 'text', array(
            'label' => $this->__('Price Step'),
            'title' => $this->__('Price Step'),
            'name' => 'option_page_price_step',
            'required' => false,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_PRICE_STEP,
            'default_label' => $this->__('Use System Configuration'),
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $fieldset = $this->addFieldset($form, 'mfs_option_custom_design', array(
            'title' => $this->__('Custom Design'),
            'legend' => $this->__('Custom Design'),
        ));

        $this->addField($fieldset, 'option_page_custom_design_active_from', 'date', array(
            'label' => $this->__('Active From'),
            'title' => $this->__('Active From'),
            'name' => 'option_page_custom_design_active_from',
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
            'required' => false,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_CUSTOM_DESIGN_ACTIVE_FROM,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'option_page_custom_design_active_to', 'date', array(
            'label' => $this->__('Active To'),
            'title' => $this->__('Active To'),
            'name' => 'option_page_custom_design_active_to',
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
            'required' => false,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_CUSTOM_DESIGN_ACTIVE_TO,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'option_page_custom_design', 'select', array(
            'label' => $this->__('Theme'),
            'title' => $this->__('Theme'),
            'values' => $this->getDesignSourceModel()->getAllOptions(),
            'name' => 'option_page_custom_design',
            'required' => false,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_CUSTOM_DESIGN,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'option_page_custom_layout_xml', 'textarea', array(
            'label' => $this->__('Layout XML'),
            'title' => $this->__('Layout XML'),
            'name' => 'option_page_custom_layout_xml',
            'required' => false,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_CUSTOM_LAYOUT_XML,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->setForm($form);
        return parent::_prepareForm();
    }
}