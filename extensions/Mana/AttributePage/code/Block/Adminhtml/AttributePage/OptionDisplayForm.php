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
class Mana_AttributePage_Block_Adminhtml_AttributePage_OptionDisplayForm extends Mana_AttributePage_Block_Adminhtml_AttributePage_AbstractForm
{
    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm() {
        $form = new Varien_Data_Form(array(
            'id' => 'mf_option_display',
            'html_id_prefix' => 'mf_option_display_',
            'field_container_id_prefix' => 'mf_option_display_tr_',
            'use_container' => true,
            'method' => 'post',
            'action' => $this->getUrl('*/*/save', array('_current' => true)),
            'field_name_suffix' => 'fields',
            'flat_model' => $this->getFlatModel(),
            'edit_model' => $this->getEditModel(),
        ));

        $fieldset = $this->addFieldset($form, 'mfs_option_product_list', array(
            'title' => $this->__('Product List'),
            'legend' => $this->__('Product List'),
        ));

//        $this->addField($fieldset, 'option_page_show_products', 'select', array(
//            'label' => $this->__('Show Products'),
//            'title' => $this->__('Show Products'),
//            'options' => $this->getYesNoSourceModel()->getOptionArray(),
//            'name' => 'option_page_show_products',
//            'required' => true,
//
//            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_SHOW_PRODUCTS,
//            'default_store_label' => $this->__('Same For All Stores'),
//        ));

        $this->addField($fieldset, 'option_page_available_sort_by', 'multiselect', array(
            'label' => $this->__('Available Sort By'),
            'title' => $this->__('Available Sort By'),
            'values' => $this->getSortBySourceModel()->getAllOptions(),
            'name' => 'option_page_available_sort_by',
            'required' => true,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_AVAILABLE_SORT_BY,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'option_page_default_sort_by', 'select', array(
            'label' => $this->__('Default Sort By'),
            'title' => $this->__('Default Sort By'),
            'options' => $this->getSortBySourceModel()->getOptionArray(),
            'name' => 'option_page_default_sort_by',
            'required' => true,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_DEFAULT_SORT_BY,
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

        $this->setForm($form);
        return parent::_prepareForm();
    }
}