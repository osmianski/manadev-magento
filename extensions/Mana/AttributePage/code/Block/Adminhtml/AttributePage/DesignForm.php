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
class Mana_AttributePage_Block_Adminhtml_AttributePage_DesignForm extends Mana_AttributePage_Block_Adminhtml_AttributePage_AbstractForm
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

        $this->addField($fieldset, 'show_alphabetic_search', 'select', array(
            'label' => $this->__('Show Alphabetic Search'),
            'title' => $this->__('Show Alphabetic Search'),
            'options' => $this->getYesNoSourceModel()->getOptionArray(),
            'name' => 'show_alphabetic_search',
            'required' => true,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_SHOW_ALPHABETIC_SEARCH,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'page_layout', 'select', array(
            'label' => $this->__('Page Layout'),
            'title' => $this->__('Page Layout'),
            'options' => $this->getPageLayoutSourceModel()->getOptionArray(),
            'name' => 'page_layout',
            'required' => false,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_PAGE_LAYOUT,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'layout_xml', 'textarea', array(
            'label' => $this->__('Layout XML'),
            'title' => $this->__('Layout XML'),
            'name' => 'layout_xml',
            'required' => false,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_LAYOUT_XML,
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

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_CUSTOM_DESIGN_ACTIVE_FROM,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'custom_design_active_to', 'date', array(
            'label' => $this->__('Active To'),
            'title' => $this->__('Active To'),
            'name' => 'custom_design_active_to',
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
            'required' => false,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_CUSTOM_DESIGN_ACTIVE_TO,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'custom_design', 'select', array(
            'label' => $this->__('Theme'),
            'title' => $this->__('Theme'),
            'values' => $this->getDesignSourceModel()->getAllOptions(),
            'name' => 'custom_design',
            'required' => false,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_CUSTOM_DESIGN,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'custom_layout_xml', 'textarea', array(
            'label' => $this->__('Layout XML'),
            'title' => $this->__('Layout XML'),
            'name' => 'custom_layout_xml',
            'required' => false,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_CUSTOM_LAYOUT_XML,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->setForm($form);
        return parent::_prepareForm();
    }

}