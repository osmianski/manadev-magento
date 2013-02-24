<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterAdvanced
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_FilterAdvanced_Block_Form extends Mana_Admin_Block_Crud_Card_Form {
    protected function _prepareForm() {
        $filter = Mage::registry('m_crud_model');

        // form - collection of fieldsets
        $form = new Varien_Data_Form(array(
            'id' => 'mf_advanced',
            'html_id_prefix' => 'mf_advanced_form_',
            'use_container' => true,
            'method' => 'post',
            'action' => $this->getUrl('*/*/save', array('_current' => true)),
            'field_name_suffix' => 'fields',
            'model' => Mage::registry('m_crud_model'),
        ));
        Mage::helper('mana_core/js')->options('edit-form', array('subforms' => array('#mf_advanced' => '#mf_advanced')));

        // result
        $this->setForm($form);
        return parent::_prepareForm();
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            if ($headBlock = $this->getLayout()->getBlock('head')) {
                $headBlock->setCanLoadTinyMce(true);
            }
        }
    }
}