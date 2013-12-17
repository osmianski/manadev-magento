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
class Mana_AttributePage_Block_Adminhtml_OptionPage_DesignForm extends Mana_AttributePage_Block_Adminhtml_OptionPage_AbstractForm
{
    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm() {
        $form = new Varien_Data_Form(array(
            'id' => 'mf_design',
            'html_id_prefix' => 'mf_design_',
            'field_container_id_prefix' => 'mf_design_tr_',
            'use_container' => true,
            'method' => 'post',
            'action' => $this->getUrl('*/*/save', array('_current' => true)),
            'field_name_suffix' => 'fields',
            'flat_model' => $this->getFlatModel(),
            'edit_model' => $this->getEditModel(),
        ));

        $fieldset = $this->addFieldset($form, 'mfs_design', array(
            'title' => $this->__('Design'),
            'legend' => $this->__('Design'),
        ));

        $this->addField($fieldset, 'page_layout', 'select', array(
            'label' => $this->__('Page Layout'),
            'title' => $this->__('Page Layout'),
            'options' => $this->getPageLayoutSourceModel()->getOptionArray(),
            'name' => 'page_layout',
            'required' => false,

            'default_bit_no' => Mana_AttributePage_Model_OptionPage_Abstract::DM_PAGE_LAYOUT,
            'default_label' => $this->__('Same For All Option Pages'),
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'layout_xml', 'textarea', array(
            'label' => $this->__('Layout XML'),
            'title' => $this->__('Layout XML'),
            'name' => 'layout_xml',
            'required' => false,

            'default_bit_no' => Mana_AttributePage_Model_OptionPage_Abstract::DM_LAYOUT_XML,
            'default_label' => $this->__('Same For All Option Pages'),
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $fieldset = $this->addFieldset($form, 'mfs_product_list', array(
            'title' => $this->__('Product List'),
            'legend' => $this->__('Product List'),
        ));

        $this->addField($fieldset, 'show_products', 'select', array(
            'label' => $this->__('Show Products'),
            'title' => $this->__('Show Products'),
            'options' => $this->getYesNoSourceModel()->getOptionArray(),
            'name' => 'show_products',
            'required' => true,

            'default_bit_no' => Mana_AttributePage_Model_OptionPage_Abstract::DM_SHOW_PRODUCTS,
            'default_label' => $this->__('Same For All Option Pages'),
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'available_sort_by', 'multiselect', array(
            'label' => $this->__('Available Sort By'),
            'title' => $this->__('Available Sort By'),
            'values' => $this->getSortBySourceModel()->getAllOptions(),
            'name' => 'available_sort_by',
            'required' => true,

            'default_bit_no' => Mana_AttributePage_Model_OptionPage_Abstract::DM_AVAILABLE_SORT_BY,
            'default_label' => $this->__('Same For All Option Pages'),
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'default_sort_by', 'select', array(
            'label' => $this->__('Default Sort By'),
            'title' => $this->__('Default Sort By'),
            'options' => $this->getSortBySourceModel()->getOptionArray(),
            'name' => 'default_sort_by',
            'required' => true,

            'default_bit_no' => Mana_AttributePage_Model_OptionPage_Abstract::DM_DEFAULT_SORT_BY,
            'default_label' => $this->__('Same For All Option Pages'),
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $fieldset = $this->addFieldset($form, 'mfs_layered_nav', array(
            'title' => $this->__('Layered Navigation'),
            'legend' => $this->__('Layered Navigation'),
        ));

        $this->addField($fieldset, 'price_step', 'text', array(
            'label' => $this->__('Price Step'),
            'title' => $this->__('Price Step'),
            'name' => 'price_step',
            'required' => false,

            'default_bit_no' => Mana_AttributePage_Model_OptionPage_Abstract::DM_PRICE_STEP,
            'default_label' => $this->__('Same For All Option Pages'),
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $fieldset = $this->addFieldset($form, 'mfs_custom_design', array(
            'title' => $this->__('Custom Design'),
            'legend' => $this->__('Custom Design'),
        ));

        $this->addField($fieldset, 'custom_design_active_from', 'date', array(
            'label' => $this->__('Active From'),
            'title' => $this->__('Active From'),
            'name' => 'custom_design_active_from',
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
            'required' => false,

            'default_bit_no' => Mana_AttributePage_Model_OptionPage_Abstract::DM_CUSTOM_DESIGN_ACTIVE_FROM,
            'default_label' => $this->__('Same For All Option Pages'),
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'custom_design_active_to', 'date', array(
            'label' => $this->__('Active To'),
            'title' => $this->__('Active To'),
            'name' => 'custom_design_active_to',
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
            'required' => false,

            'default_bit_no' => Mana_AttributePage_Model_OptionPage_Abstract::DM_CUSTOM_DESIGN_ACTIVE_TO,
            'default_label' => $this->__('Same For All Option Pages'),
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'custom_design', 'select', array(
            'label' => $this->__('Theme'),
            'title' => $this->__('Theme'),
            'values' => $this->getDesignSourceModel()->getAllOptions(),
            'name' => 'custom_design',
            'required' => false,

            'default_bit_no' => Mana_AttributePage_Model_OptionPage_Abstract::DM_CUSTOM_DESIGN,
            'default_label' => $this->__('Same For All Option Pages'),
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'custom_layout_xml', 'textarea', array(
            'label' => $this->__('Layout XML'),
            'title' => $this->__('Layout XML'),
            'name' => 'custom_layout_xml',
            'required' => false,

            'default_bit_no' => Mana_AttributePage_Model_OptionPage_Abstract::DM_CUSTOM_LAYOUT_XML,
            'default_label' => $this->__('Same For All Option Pages'),
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->setForm($form);
        return parent::_prepareForm();
    }

}