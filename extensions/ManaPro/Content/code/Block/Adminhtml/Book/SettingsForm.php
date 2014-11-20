<?php
/** 
 * @category    Mana
 * @package     ManaPro_Content
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_Content_Block_Adminhtml_Book_SettingsForm extends Mana_Content_Block_Adminhtml_Book_AbstractForm
{
    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm() {
        $form = new Varien_Data_Form(array(
            'id' => 'mf_settings',
            'html_id_prefix' => 'mf_settings_',
            'field_container_id_prefix' => 'mf_settings_tr_',
            'use_container' => true,
            'method' => 'post',
            'action' => $this->getUrl('*/*/save', array('_current' => true)),
            'field_name_suffix' => 'fields',
            'flat_model' => $this->getFlatModel(),
            'edit_model' => $this->getEditModel(),
        ));

        $fieldset = $this->addFieldset($form, 'mfs_general', array(
            'title' => $this->__('General'),
            'legend' => $this->__('General'),
        ));

        $this->addField($fieldset, 'url_key', 'text', array(
            'label' => $this->__('URL Key'),
            'title' => $this->__('URL Key'),
            'name' => 'url_key',
            'required' => true,

            'default_bit_no' => Mana_Content_Model_Page_Abstract::DM_URL_KEY,
            'default_label' => $this->__('Use Title'),
            'default_store_label' => $this->__('Use Title'),
        ));

        $is_active = array(
            'label' => $this->__('Status'),
            'title' => $this->__('Status'),
            'options' => $this->getStatusSourceModel()->getOptionArray(),
            'name' => 'is_active',
            'required' => true,
            'default_bit_no' => Mana_Content_Model_Page_Abstract::DM_IS_ACTIVE,
        );
        $model = ($this->adminHelper()->isGlobal()) ? Mage::registry('m_edit_model') : Mage::registry('m_global_edit_model');

        if(!is_null($this->getRequest()->getParam('id')) && $model->getData('page_global_id') != $this->getRequest()->getParam('id')) {
            //!is_null($model->getData('parent_id'))
            $is_active['default_label'] = $this->__('Use Parent Page');
            $is_active['default_store_label'] = $this->__('Use Parent Page');
            $is_active['note'] = $this->__('If `Use Parent Page` is checked, value for Status will cascade after save.');
        }

        $this->addField($fieldset, 'is_active', 'select', $is_active);

        if(is_null($model->getData('parent_id'))) {
            $this->addField($fieldset, 'position', 'text', array(
                'label' => $this->__('Position'),
                'title' => $this->__('Position'),
                'name' => 'position',

                'default_bit_no' => Mana_Content_Model_Page_Abstract::DM_POSITION,
                'default_store_label' => $this->__('Same For All Stores'),
            ));
        }

        $fieldsetSeo = $this->addFieldset($form, 'mfs_seo', array(
            'title' => $this->__('Seo'),
            'legend' => $this->__('Seo'),
        ));

        $this->addField($fieldsetSeo, 'meta_title', 'text', array(
            'label' => $this->__('Meta Title'),
            'title' => $this->__('Meta Title'),
            'name' => 'meta_title',
            'required' => false,

            'default_bit_no' => Mana_Content_Model_Page_Abstract::DM_META_TITLE,
            'default_label' => $this->__('Use Title'),
            'default_store_label' => $this->__('Use Title'),
        ));

        $this->addField($fieldsetSeo, 'meta_description', 'textarea', array(
            'label' => $this->__('Meta Description'),
            'title' => $this->__('Meta Description'),
            'name' => 'meta_description',
            'required' => false,

            'default_bit_no' => Mana_Content_Model_Page_Abstract::DM_META_DESCRIPTION,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldsetSeo, 'meta_keywords', 'textarea', array(
            'label' => $this->__('Meta Keywords'),
            'title' => $this->__('Meta Keywords'),
            'name' => 'meta_keywords',
            'required' => false,

            'default_bit_no' => Mana_Content_Model_Page_Abstract::DM_META_KEYWORDS,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->setForm($form);
        return parent::_prepareForm();
    }
}