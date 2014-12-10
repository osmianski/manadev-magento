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
class Mana_Seo_Block_Adminhtml_Schema_UrlForm extends Mana_Admin_Block_V2_Form {
    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm() {
        $form = new Varien_Data_Form(array(
            'id' => 'mf_url',
            'html_id_prefix' => 'mf_url_',
            'field_container_id_prefix' => 'mf_url_tr_',
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

        if ($this->coreHelper()->isManadevSeoLayeredNavigationInstalled()) {
            $this->addField($fieldset, 'sample', 'label', array(
                'label' => $this->__('Sample URL'),
                'note' => $this->__("Demonstrates how URL separators and parameters/filters of different kind appear in URL"),
                'name' => 'sample',
                'bold' => true,
            ));
        }
        $this->addField($fieldset, 'name', 'text', array(
            'label' => $this->__('Name'),
            'name' => 'name',
            'required' => true,
        ));
        if ($this->adminHelper()->isGlobal()) {
            $this->addField($fieldset, 'status', 'select', array_merge(array(
                'options' => $this->getStatusSourceModel()->getOptionArray(),
                'label' => $this->__('Status'),
                'name' => 'status',
                'required' => true,
            ), $this->getFlatModel()->getStatus() == Mana_Seo_Model_Schema::STATUS_ACTIVE ? array(
                'disabled' => true,
                'note' => $this->__("You can't change status of active schema. However, you can set another 'Redirect' schema as 'Active' (after this active schema status automatically changes to 'Redirect')."),
            ) : array(
                'note' => $this->__("If changed to 'Active', previously active schema status is set to 'Redirect'."),
            )));
        }
        else {
            $this->addField($fieldset, 'status', 'select', array(
                'options' => $this->getStatusSourceModel()->getOptionArray(),
                'label' => $this->__('Status'),
                'name' => 'status',
                'required' => true,
                'disabled' => true,
                'hide_use_default' => true,
                'note' => $this->__("You can't change status on store level."),
            ));
        }


        if ($this->coreHelper()->isManadevSeoLayeredNavigationInstalled()) {
            $fieldset = $this->addFieldset($form, 'mfs_separator', array(
                'title' => $this->__('Separators'),
                'legend' => $this->__('Separators'),
            ));
            $this->addField($fieldset, 'query_separator', 'text', array(
                'label' => $this->__('Query Separator'),
                'name' => 'query_separator',
                'required' => true,
            ));
            $this->addField($fieldset, 'param_separator', 'text', array(
                'label' => $this->__('Parameter Separator'),
                'name' => 'param_separator',
                'required' => true,
            ));
            $this->addField($fieldset, 'first_value_separator', 'text', array(
                'label' => $this->__('Value Separator'),
                'name' => 'first_value_separator',
                'required' => true,
            ));
            $this->addField($fieldset, 'multiple_value_separator', 'text', array(
                'label' => $this->__('Multiple Value Separator'),
                'name' => 'multiple_value_separator',
                'required' => true,
            ));
            $this->addField($fieldset, 'price_separator', 'text', array(
                'label' => $this->__('Price Separator'),
                'name' => 'price_separator',
                'required' => true,
            ));
            $this->addField($fieldset, 'category_separator', 'text', array(
                'label' => $this->__('Category Separator'),
                'note' => $this->__('Used when filtering by category (Redirect to Subcategory Page = No) to separate 2 or more subcategories'),
                'name' => 'category_separator',
                'required' => true,
            ));

            $fieldset = $this->addFieldset($form, 'mfs_redirect', array(
                'title' => $this->__('Redirects'),
                'legend' => $this->__('Redirects'),
            ));
            $this->addField($fieldset, 'redirect_parameter_order', 'select', array(
                'options' => $this->getYesNoSourceModel()->getOptionArray(),
                'label' => $this->__('Redirect To Same Page With Correct Parameter Order'),
                'name' => 'redirect_parameter_order',
                'required' => true,
            ));
            $this->addField($fieldset, 'redirect_to_subcategory', 'select', array(
                'options' => $this->getYesNoSourceModel()->getOptionArray(),
                'label' => $this->__('Redirect Applied Category Filters To Subcategory Page'),
                'name' => 'redirect_to_subcategory',
                'required' => true,
            ));

            $fieldset = $this->addFieldset($form, 'mfs_other', array(
                'title' => $this->__('Other'),
                'legend' => $this->__('Other'),
            ));
            $this->addField($fieldset, 'include_filter_name', 'select', array(
                'options' => $this->getYesNoSourceModel()->getOptionArray(),
                'label' => $this->__('Include Filter Names Before Values'),
                'note' => $this->__('Only applicable to attribute-based filters'),
                'name' => 'include_filter_name',
                'required' => true,
            ));
            $this->addField($fieldset, 'use_filter_labels', 'select', array(
                'options' => $this->getYesNoSourceModel()->getOptionArray(),
                'label' => $this->__('Use Attribute Labels Instead Of Attribute Codes'),
                'name' => 'use_filter_labels',
                'required' => true,
            ));
            $this->addField($fieldset, 'use_range_bounds', 'select', array(
                'options' => $this->getYesNoSourceModel()->getOptionArray(),
                'label' => $this->__('Use Range Bounds in Price Filters'),
                'name' => 'use_range_bounds',
                'required' => true,
            ));
            $this->addField($fieldset, 'accent_insensitive', 'select', array(
                'options' => $this->getYesNoSourceModel()->getOptionArray(),
                'label' => $this->__('Accent Insensitive (Deprecated)'),
                'name' => 'accent_insensitive',
                'required' => true,
            ));
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }

    #region Dependencies
    /**
     * @return Mana_Seo_Model_Schema
     */
    public function getFlatModel() {
        return Mage::registry('m_flat_model');
    }

    /**
     * @return Mana_Seo_Model_Schema
     */
    public function getEditModel() {
        return Mage::registry('m_edit_model');
    }

    /**
     * @return Mana_Seo_Model_Source_Schema_Status
     */
    public function getStatusSourceModel() {
        return Mage::getSingleton('mana_seo/source_schema_explainedStatus');
    }
    #endregion
}