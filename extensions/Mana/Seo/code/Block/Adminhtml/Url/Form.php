<?php
/** 
 * @category    Mana
 * @package     Mana_Seo
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Seo_Block_Adminhtml_Url_Form extends Mana_Admin_Block_V2_Form {
    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm() {
        $form = new Varien_Data_Form(array(
            'id' => 'mf_form',
            'html_id_prefix' => 'mf_form_',
            'use_container' => true,
            'method' => 'post',
            'action' => $this->getUrl('*/*/save', array('_current' => true)),
            'field_name_suffix' => 'fields',
            'field_container_id_prefix' => 'mf_form_tr_',
            'flat_model' => $this->getFlatModel(),
            'edit_model' => $this->getEditModel(),
        ));

        $fieldset = $this->addFieldset($form, 'mfs_general', array(
            'title' => $this->__('General'),
            'legend' => $this->__('General'),
        ));
        $this->addField($fieldset, 'description', 'label', array(
            'label' => $this->__('Description'),
            'name' => 'description',
            'bold' => true,
        ));
        $this->addField($fieldset, 'status', 'select_text', array(
            'options' => $this->getStatusSourceModel()->getOptionArray(),
            'label' => $this->__('Status'),
            'name' => 'status',
            'bold' => true,
        ));
        $this->addField($fieldset, 'position', 'label', array(
            'label' => $this->__('Position'),
            'name' => 'position',
            'bold' => true,
        ));
        $this->addField($fieldset, 'global_schema_id', 'select_text', array(
            'options' => $this->getSchemas(),
            'label' => $this->__('Schema'),
            'name' => 'global_schema_id',
            'bold' => true,
        ));
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addField($fieldset, 'store_id', 'select_text', array(
                'options' => $this->getStoreSourceModel()->load()->toOptionHash(),
                'label' => $this->__('Store'),
                'name' => 'store_id',
                'bold' => true,
            ));
        }

        $fieldset = $this->addFieldset($form, 'mfs_url_key', array(
            'title' => $this->__('URL Key'),
            'legend' => $this->__('URL Key'),
        ));
        $this->addField($fieldset, 'url_key', 'label', array(
            'label' => $this->__('Default URL Key'),
            'name' => 'url_key',
            'bold' => true,
        ));
        if (!($this->getFlatModel()->getData('is_page') && $this->getFlatModel()->getData('type') == 'category')) {
            $this->addField($fieldset, 'manual_url_key', 'text', array(
                'label' => $this->__('Manual URL Key'),
                'name' => 'manual_url_key',
                'note' => $this->__('If not empty, this is used instead of default URL key'),
            ));
            $this->addField($fieldset, 'final_url_key', 'label', array(
                'label' => $this->__('Actually Used URL Key'),
                'name' => 'final_url_key',
                'bold' => true,
            ));
        }

        if ($this->getFlatModel()->getData('is_attribute_value')) {
            $fieldset = $this->addFieldset($form, 'mfs_include_filter_name', array(
                'title' => $this->__('Include Filter Name'),
                'legend' => $this->__('Include Filter Name'),
            ));
            $this->addField($fieldset, 'include_filter_name', 'select_text', array(
                'options' => $this->getYesNoSourceModel()->getOptionArray(),
                'label' => $this->__('Default Value'),
                'name' => 'include_filter_name',
                'bold' => true,
            ));
            $this->addField($fieldset, 'force_include_filter_name', 'select', array(
                'options' => $this->getYesNoDefaultSourceModel()->getOptionArray(),
                'label' => $this->__('Manual Value'),
                'name' => 'force_include_filter_name',
                'note' => $this->__("If not equal to 'Use Default', this is used instead of default value"),
            ));
            $this->addField($fieldset, 'final_include_filter_name', 'select_text', array(
                'options' => $this->getYesNoSourceModel()->getOptionArray(),
                'label' => $this->__('Actually Used Value'),
                'name' => 'final_include_filter_name',
                'bold' => true,
            ));
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }

    public function getSchemas() {
        return $this->createSchemaCollection()
            ->load()
            ->toOptionHash();
    }

    #region Dependencies
    /**
     * @return Mana_Seo_Model_Url
     */
    public function getFlatModel() {
        return Mage::registry('m_flat_model');
    }

    /**
     * @return Mana_Seo_Model_Url
     */
    public function getEditModel() {
        return Mage::registry('m_edit_model');
    }

    /**
     * @return Mana_Seo_Model_Source_Schema_Status
     */
    public function getStatusSourceModel() {
        return Mage::getSingleton('mana_seo/source_url_status');
    }

    /**
     * @return Mana_Admin_Resource_Store_Collection
     */
    public function getStoreSourceModel() {
        return Mage::getResourceSingleton('mana_admin/store_collection');
    }
    public function createSchemaCollection() {
        return $this->dbHelper()->getResourceModel('mana_seo/schema/flat_collection');
    }

    /**
     * @return Mana_Core_Model_Source_Yesno
     */
    public function getYesNoSourceModel() {
        return Mage::getSingleton('mana_core/source_yesno');
    }

    /**
     * @return Mana_Core_Model_Source_YesNoDefault
     */
    public function getYesNoDefaultSourceModel() {
        return Mage::getSingleton('mana_core/source_yesNoDefault');
    }
    #endregion
}