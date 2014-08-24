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
class Mana_AttributePage_Block_Adminhtml_AttributePage_DisplayForm extends Mana_AttributePage_Block_Adminhtml_AttributePage_AbstractForm
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

        $fieldset = $this->addFieldset($form, 'mfs_display', array(
            'title' => $this->__('Display'),
            'legend' => $this->__('Display'),
        ));

        $this->addField($fieldset, 'show_alphabetic_search', 'select', array(
            'label' => $this->__('Show Alphabetic Search'),
            'title' => $this->__('Show Alphabetic Search'),
            'note' => $this->__('If Yes, then alphabet is shown on top of attribute page'),
            'options' => $this->getYesNoSourceModel()->getOptionArray(),
            'name' => 'show_alphabetic_search',
            'required' => true,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_SHOW_ALPHABETIC_SEARCH,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'show_featured_options', 'select', array(
            'label' => $this->__('Show Featured Images'),
            'title' => $this->__('Show Featured Images'),
            'note' => $this->__('If Yes, then featured attribute option images (as specified in MANAdev->Attribute Pages->Option Page ([attribute name]) menu) are shown on top of attribute page'),
            'options' => $this->getYesNoSourceModel()->getOptionArray(),
            'name' => 'show_featured_options',
            'required' => true,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_SHOW_FEATURED_OPTIONS,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'hide_empty_option_pages', 'select', array(
            'label' => $this->__('Hide Empty Option Pages'),
            'title' => $this->__('Hide Empty Option Pages'),
            'options' => $this->getYesNoSourceModel()->getOptionArray(),
            'name' => 'hide_empty_option_pages',
            'required' => true,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_HIDE_EMPTY_OPTION_PAGES,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'column_count', 'text', array(
            'label' => $this->__('Column Count'),
            'title' => $this->__('Column Count'),
            'note' => $this->__('Into how many columns option list on attribute page is divided, uses standard Magento CSS classes col1-set etc. <strong>Typically themes do not support more that 4 columns. This setting is only applied when Template 1 is used.</strong>'),
            'name' => 'column_count',
            'required' => true,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_COLUMN_COUNT,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $fieldset = $this->addFieldset($form, 'mfs_options_pre_page', array(
            'title' => $this->__('Options per Page'),
            'legend' => $this->__('Options per Page'),
        ));

        $this->addField($fieldset, 'allowed_page_sizes', 'text', array(
            'label' => $this->__('Allowed Values'),
            'title' => $this->__('Allowed Values'),
            'note' => $this->__('Write one or more numbers or <code>all</code> separated by comma'),
            'name' => 'allowed_page_sizes',
            'required' => true,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_ALLOWED_PAGE_SIZES,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'default_page_size', 'text', array(
            'label' => $this->__('Default Value'),
            'title' => $this->__('Default Value'),
            'note' => $this->__('One of values in Allowed Values field'),
            'name' => 'default_page_size',
            'required' => true,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_ALLOWED_PAGE_SIZES,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->setForm($form);
        return parent::_prepareForm();
    }

}