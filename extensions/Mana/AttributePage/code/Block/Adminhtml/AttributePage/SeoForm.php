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
class Mana_AttributePage_Block_Adminhtml_AttributePage_SeoForm extends Mana_AttributePage_Block_Adminhtml_AttributePage_AbstractForm
{
    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm() {
        $form = new Varien_Data_Form(array(
            'id' => 'mf_seo',
            'html_id_prefix' => 'mf_seo_',
            'field_container_id_prefix' => 'mf_seo_tr_',
            'use_container' => true,
            'method' => 'post',
            'action' => $this->getUrl('*/*/save', array('_current' => true)),
            'field_name_suffix' => 'fields',
            'flat_model' => $this->getFlatModel(),
            'edit_model' => $this->getEditModel(),
        ));

        $fieldset = $this->addFieldset($form, 'mfs_seo', array(
            'title' => $this->__('SEO'),
            'legend' => $this->__('SEO'),
        ));

        $this->addField($fieldset, 'url_key', 'text', array(
            'label' => $this->__('URL Key'),
            'title' => $this->__('URL Key'),
            'name' => 'url_key',
            'required' => true,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_URL_KEY,
            'default_label' => $this->__('Use Attribute Labels'),
            'default_store_label' => $this->__('Use Attribute Labels'),
        ));

        $this->addField($fieldset, 'meta_title', 'text', array(
            'label' => $this->__('Page Title'),
            'title' => $this->__('Page Title'),
            'name' => 'meta_title',
            'required' => true,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_META_TITLE,
            'default_label' => $this->__('Use Title'),
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'meta_description', 'textarea', array(
            'label' => $this->__('Meta Description'),
            'title' => $this->__('Meta Description'),
            'name' => 'meta_description',
            'required' => false,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_META_DESCRIPTION,
            'default_label' => $this->__('Use Description'),
            'default_store_label' => $this->__('Use Description'),
        ));

        $this->addField($fieldset, 'meta_keywords', 'textarea', array(
            'label' => $this->__('Meta Keywords'),
            'title' => $this->__('Meta Keywords'),
            'name' => 'meta_keywords',
            'required' => false,

            'default_bit_no' => Mana_AttributePage_Model_AttributePage_Abstract::DM_META_KEYWORDS,
            'default_label' => $this->__('Use Attribute Labels'),
            'default_store_label' => $this->__('Use Attribute Labels'),
        ));

        $this->setForm($form);
        return parent::_prepareForm();
    }
}