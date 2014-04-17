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
class ManaPro_FilterContent_Block_Adminhtml_Filter_ContentForm  extends Mana_Admin_Block_V3_Form
{
    protected $_id;
    protected $_model;

    public function init($formId, $model) {

        $this->_id = $formId;
        $this->_model = $model;
        return $this;
    }

    protected function _prepareLayout() {
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            if ($head = $this->getLayout()->getBlock('head')) {
                $head->setData('can_load_tiny_mce', true);
            }
        }
    }

    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm() {
        $form = new Varien_Data_Form(array(
            'id' => $this->_id . '_',
            'html_id_prefix' => $this->_id . '_',
            'field_container_id_prefix' => $this->_id . '_tr_',
            'use_container' => true,
            'method' => 'post',
            'action' => $this->getUrl('*/*/save', array('_current' => true)),
            'field_name_suffix' => 'fields',
            'flat_model' => $this->_model,
            'edit_model' => $this->_model,
        ));


        $fieldset = $this->addFieldset($form, $this->_id . '_mfs_seo', array(
            'title' => $this->__('Option Content'),
            'legend' => $this->__('Option Content'),
        ));

        $this->addField($fieldset, 'content_is_initialized', 'hidden', array(
            'name' => 'content_is_initialized'
        ));

        $this->addField($fieldset, 'content_title', 'textarea', array(
            'name' => 'content_title',
            'label' => $this->__('Title (H1)'),
            'title' => $this->__('Title (H1)'),
            'required' => false,

            'default_bit_no' => Mana_Filters_Resource_Filter2_Value::DM_CONTENT_TITLE,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'content_subtitle', 'wysiwyg', array(
            'name' => 'content_subtitle',
            'label' => $this->__('Subtitle'),
            'title' => $this->__('Subtitle'),
            'required' => false,

            'default_bit_no' => Mana_Filters_Resource_Filter2_Value::DM_CONTENT_SUBTITLE,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'content_description', 'wysiwyg', array(
            'name' => 'content_description',
            'label' => $this->__('Description'),
            'title' => $this->__('Description'),
            'required' => false,

            'default_bit_no' => Mana_Filters_Resource_Filter2_Value::DM_CONTENT_DESCRIPTION,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'content_additional_description', 'wysiwyg', array(
            'name' => 'content_additional_description',
            'label' => $this->__('Additional Description'),
            'title' => $this->__('Additional Description'),
            'required' => false,

            'default_bit_no' => Mana_Filters_Resource_Filter2_Value::DM_CONTENT_ADDITIONAL_DESCRIPTION,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'content_background_image', 'image', array(
            'name' => 'content_background_image',
            'label' => $this->__('Background Image for Additional Description'),
            'title' => $this->__('Background Image for Additional Description'),
            'required' => false,

            'default_bit_no' => Mana_Filters_Resource_Filter2_Value::DM_CONTENT_BACKGROUND_IMAGE,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'content_meta_title', 'textarea', array(
            'name' => 'content_meta_title',
            'label' => $this->__('Page Title'),
            'title' => $this->__('Page Title'),
            'required' => false,

            'default_bit_no' => Mana_Filters_Resource_Filter2_Value::DM_CONTENT_META_TITLE,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'content_meta_description', 'textarea', array(
            'name' => 'content_meta_description',
            'label' => $this->__('Meta Description'),
            'title' => $this->__('Meta Description'),
            'required' => false,

            'default_bit_no' => Mana_Filters_Resource_Filter2_Value::DM_CONTENT_META_DESCRIPTION,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'content_meta_keywords', 'textarea', array(
            'name' => 'content_meta_keywords',
            'label' => $this->__('Meta Keywords'),
            'title' => $this->__('Meta Keywords'),
            'required' => false,

            'default_bit_no' => Mana_Filters_Resource_Filter2_Value::DM_CONTENT_META_KEYWORDS,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'content_meta_robots', 'textarea', array(
            'name' => 'content_meta_robots',
            'label' => $this->__('Meta Robots'),
            'title' => $this->__('Meta Robots'),
            'required' => false,

            'default_bit_no' => Mana_Filters_Resource_Filter2_Value::DM_CONTENT_META_ROBOTS,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'content_common_directives', 'textarea', array(
            'name' => 'content_common_directives',
            'label' => $this->__('Common Directives'),
            'title' => $this->__('Common Directives'),
            'required' => false,

            'default_bit_no' => Mana_Filters_Resource_Filter2_Value::DM_CONTENT_COMMON_DIRECTIVES,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

//        $this->addField($fieldset, 'content_layout_xml', 'textarea', array(
//            'name' => 'content_layout_xml',
//            'label' => $this->__('Layout XML'),
//            'title' => $this->__('Layout XML'),
//            'required' => false,
//        ));

        $this->setForm($form);
        return parent::_prepareForm();
    }

    protected function _initFormValues() {
        parent::_initFormValues();
        foreach ($this->getForm()->getElements() as $element) {
            $element->unsetData('dirty');
        }
        if (!$this->_model->getData('content_is_initialized')) {
            $initialValues = array(
                'content_is_initialized' => 1
            );
            foreach ($this->factoryHelper()->getAllContent() as $key => $contentHelper) {
                if ($contentHelper->isContentReplaced()) {
                    $initialValues['content_' . $key] = '{{ ' . $key . ' }}';
                }
            }

            $this->getForm()->addValues($initialValues);
            foreach (array_keys($initialValues) as $field) {
                $this->getForm()->getElement($field)->setData('dirty', 1);
            }
        }

        return $this;
    }

    #region Dependencies
    /**
     * @return Mana_Core_Model_Source_Status
     */
    public function getStatusSourceModel() {
        return Mage::getSingleton('mana_core/source_status');
    }

    /**
     * @return ManaPro_FilterContent_Helper_Factory
     */
    public function factoryHelper() {
        return Mage::helper('manapro_filtercontent/factory');
    }

    #endregion
}