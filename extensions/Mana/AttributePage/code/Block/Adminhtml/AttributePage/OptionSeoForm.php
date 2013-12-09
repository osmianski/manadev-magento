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
class Mana_AttributePage_Block_Adminhtml_AttributePage_OptionSeoForm extends Mana_AttributePage_Block_Adminhtml_AttributePage_AbstractForm
{
    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm() {
        $form = new Varien_Data_Form(array(
            'id' => 'mf_option_seo',
            'html_id_prefix' => 'mf_option_seo_',
            'field_container_id_prefix' => 'mf_option_seo_tr_',
            'use_container' => true,
            'method' => 'post',
            'action' => $this->getUrl('*/*/save', array('_current' => true)),
            'field_name_suffix' => 'fields',
            'flat_model' => $this->getFlatModel(),
            'edit_model' => $this->getEditModel(),
        ));

        $fieldset = $this->addFieldset($form, 'mfs_option_seo', array(
            'title' => $this->__('SEO'),
            'legend' => $this->__('SEO'),
        ));

        $this->addField($fieldset, 'option_page_include_filter_name', 'select', array(
            'label' => $this->__('Include Attribute Name In URL Key'),
            'title' => $this->__('Include Attribute Name In URL Key'),
            'name' => 'option_page_include_filter_name',
            'options' => $this->getYesNoSourceModel()->getOptionArray(),
            'required' => true,
        ));

        $this->setForm($form);
        return parent::_prepareForm();
    }

}