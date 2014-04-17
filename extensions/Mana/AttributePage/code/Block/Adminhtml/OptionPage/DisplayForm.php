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
class Mana_AttributePage_Block_Adminhtml_OptionPage_DisplayForm extends Mana_AttributePage_Block_Adminhtml_OptionPage_AbstractForm
{
    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm() {
        $form = new Varien_Data_Form(array(
            'id' => 'mf_display',
            'html_id_prefix' => 'mf_display_',
            'field_container_id_prefix' => 'mf_display_tr_',
            'use_container' => true,
            'method' => 'post',
            'action' => $this->getUrl('*/*/save', array('_current' => true)),
            'field_name_suffix' => 'fields',
            'flat_model' => $this->getFlatModel(),
            'edit_model' => $this->getEditModel(),
        ));

        $fieldset = $this->addFieldset($form, 'mfs_product_list', array(
            'title' => $this->__('Product List'),
            'legend' => $this->__('Product List'),
        ));

//        $this->addField($fieldset, 'show_products', 'select', array(
//            'label' => $this->__('Show Products'),
//            'title' => $this->__('Show Products'),
//            'options' => $this->getYesNoSourceModel()->getOptionArray(),
//            'name' => 'show_products',
//            'required' => true,
//
//            'default_bit_no' => Mana_AttributePage_Model_OptionPage_Abstract::DM_SHOW_PRODUCTS,
//            'default_label' => $this->__('Same For All Option Pages'),
//            'default_store_label' => $this->__('Same For All Stores'),
//        ));

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

        $this->setForm($form);
        return parent::_prepareForm();
    }

}