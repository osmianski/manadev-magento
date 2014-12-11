<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterContent
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_FilterContent_Block_Adminhtml_ConditionForm  extends ManaPro_FilterContent_Block_Adminhtml_AbstractForm
{
    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm() {
        $form = new Varien_Data_Form(array(
            'id' => 'mf_condition',
            'html_id_prefix' => 'mf_condition_',
            'field_container_id_prefix' => 'mf_condition_tr_',
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

        $this->addField($fieldset, 'priority', 'text', array(
            'label' => $this->__('Priority'),
            'title' => $this->__('Priority'),
            'name' => 'priority',
            'required' => true,
        ));

        $this->addField($fieldset, 'is_active', 'select', array(
            'label' => $this->__('Status'),
            'title' => $this->__('Status'),
            'options' => $this->getStatusSourceModel()->getOptionArray(),
            'name' => 'is_active',
            'required' => true,
        ));

        $this->addField($fieldset, 'stop', 'select', array(
            'name' => 'stop',
            'label' => $this->__('Stop Further Processing'),
            'title' => $this->__('Stop Further Processing'),
            'options' => $this->getYesNoSourceModel()->getOptionArray(),
            'required' => true,
        ));

        $fieldset = $this->addFieldset($form, 'mfs_condition', array(
            'title' => $this->__('Conditions'),
            'legend' => $this->__('Conditions'),
            'renderer' => 'mana_admin/v2_fieldset_condition',
            'new_child_url' => $this->getUrl('*/*/newCondition/form/mf_condition_mfs_condition'),
        ));

        $this->addField($fieldset, 'conditions', 'text', array(
            'name' => 'conditions',
            'label' => $this->__('Conditions'),
            'title' => $this->__('Conditions'),
            'required' => true,
            'renderer' => 'mana_admin/v2_field_condition',
        ));

        $fieldset = $this->addFieldset($form, 'mfs_seo', array(
            'title' => $this->__('SEO'),
            'legend' => $this->__('SEO'),
        ));

        $this->addField($fieldset, 'meta_title', 'text', array(
            'name' => 'meta_title',
            'label' => $this->__('Page Title'),
            'title' => $this->__('Page Title'),
            'required' => false,
        ));

        $this->addField($fieldset, 'meta_description', 'textarea', array(
            'name' => 'meta_description',
            'label' => $this->__('Meta Description'),
            'title' => $this->__('Meta Description'),
            'required' => false,
        ));

        $this->addField($fieldset, 'meta_keywords', 'textarea', array(
            'name' => 'meta_keywords',
            'label' => $this->__('Meta Keywords'),
            'title' => $this->__('Meta Keywords'),
            'required' => false,
        ));

        $fieldset = $this->addFieldset($form, 'mfs_content', array(
            'title' => $this->__('Content'),
            'legend' => $this->__('Content'),
        ));

        $this->addField($fieldset, 'title', 'text', array(
            'name' => 'title',
            'label' => $this->__('Title (H1)'),
            'title' => $this->__('Title (H1)'),
            'required' => false,
        ));

        $this->addField($fieldset, 'description', 'textarea', array(
            'name' => 'description',
            'label' => $this->__('Description'),
            'title' => $this->__('Description'),
            'required' => false,
        ));

        $fieldset = $this->addFieldset($form, 'mfs_layout', array(
            'title' => $this->__('Layout'),
            'legend' => $this->__('Layout'),
        ));

        $this->addField($fieldset, 'layout_xml', 'textarea', array(
            'name' => 'layout_xml',
            'label' => $this->__('Layout XML'),
            'title' => $this->__('Layout XML'),
            'required' => false,
        ));

        $this->setForm($form);
        return parent::_prepareForm();
    }

    #region Dependencies
    /**
     * @return Mana_Core_Model_Source_Status
     */
    public function getStatusSourceModel() {
        return Mage::getSingleton('mana_core/source_status');
    }

    #endregion
}