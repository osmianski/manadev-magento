<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterAdmin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Enter description here ...
 * @author Mana Team
 *
 */
class ManaPro_FilterAdmin_Block_Card_General extends Mana_Admin_Block_Crud_Card_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface {
	/**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm() {
		// form - collection of fieldsets
		$form = new Varien_Data_Form(array(
			'id' => 'mf_general',
			'html_id_prefix' => 'mf_general_',
			'use_container' => true,
			'method' => 'post',
			'action' => $this->getUrl('*/*/save', array('_current' => true)),
			'field_name_suffix' => 'fields',
			'model' => $this->getModel(),
		));
        /** @noinspection PhpUndefinedMethodInspection */
        Mage::helper('mana_core/js')->options('edit-form', array('subforms' => array('#mf_general' => '#mf_general')));
		
		// fieldset - collection of fields
        /** @noinspection PhpParamsInspection */
        $fieldset = $form->addFieldset('mfs_general', array(
			'title' => $this->__('General Information'),
			'legend' => $this->__('General Information'),
		));
		$fieldset->setRenderer($this->getLayout()->getBlockSingleton('mana_admin/crud_card_fieldset'));

        /** @noinspection PhpUndefinedMethodInspection */
        $field = $fieldset->addField('name', 'text', array(
			'label' => $this->__('Name'),
			'name' => 'name',
			'required' => true,
			'default_bit' => Mana_Filters_Resource_Filter2::DM_NAME,
			'default_label' => Mage::helper('mana_admin')->isGlobal() 
				? ($this->getModel()->getType() != 'category' ? $this->__('Use Attribute Configuration') : $this->__('Use Default'))
				: ($this->getModel()->getType() != 'category' ? $this->__('Use Attribute Configuration') : $this->__('Use Default')),
		));
		$field->setRenderer($this->getLayout()->getBlockSingleton('mana_admin/crud_card_field'));
		
		$field = $fieldset->addField('is_enabled', 'select', array(
			'label' => $this->__('In Category'),
			'name' => 'is_enabled',
			'required' => true,
			'options' => Mage::getSingleton('mana_filters/source_filterable')->getOptionArray(),
			'default_bit' => Mana_Filters_Resource_Filter2::DM_IS_ENABLED,
			'default_label' => Mage::helper('mana_admin')->isGlobal() 
				? ($this->getModel()->getType() != 'category' ? $this->__('Use Attribute Configuration') : $this->__('Use Default'))
				: $this->__('Same For All Stores'),
		));
        /** @noinspection PhpParamsInspection */
        $field->setRenderer($this->getLayout()->getBlockSingleton('mana_admin/crud_card_field'));

        /** @noinspection PhpUndefinedMethodInspection */
        $field = $fieldset->addField('is_enabled_in_search', 'select', array(
			'label' => $this->__('In Search'),
			'name' => 'is_enabled_in_search',
			'required' => true,
			'options' => Mage::getSingleton('mana_filters/source_filterable')->getOptionArray(),
			'default_bit' => Mana_Filters_Resource_Filter2::DM_IS_ENABLED_IN_SEARCH,
			'default_label' => Mage::helper('mana_admin')->isGlobal() 
				? ($this->getModel()->getType() != 'category' ? $this->__('Use Attribute Configuration') : $this->__('Use Default'))
				: $this->__('Same For All Stores'),
		));
		$field->setRenderer($this->getLayout()->getBlockSingleton('mana_admin/crud_card_field'));
		
		// fieldset - collection of fields
        /** @noinspection PhpParamsInspection */
		$fieldset = $form->addFieldset('mfs_display', array(
			'title' => $this->__('Display Settings'),
			'legend' => $this->__('Display Settings'),
		));
		$fieldset->setRenderer($this->getLayout()->getBlockSingleton('mana_admin/crud_card_fieldset'));

        $field = $fieldset->addField('display', 'select', array(
			'label' => $this->__('Display'),
			'name' => 'display',
			'required' => true,
			'options' => Mage::getSingleton('mana_filters/source_display_'.$form->getModel()->getType())->getOptionArray(),
			'default_bit' => Mana_Filters_Resource_Filter2::DM_DISPLAY,
			'default_label' => Mage::helper('mana_admin')->isGlobal() 
				? $this->__('Use System Configuration') 
				: $this->__('Same For All Stores'),
		));
        /** @noinspection PhpParamsInspection */
        $field->setRenderer($this->getLayout()->getBlockSingleton('mana_admin/crud_card_field'));
		
		$field = $fieldset->addField('position', 'text', array(
			'label' => $this->__('Position'),
			'name' => 'position',
			'required' => true,
			'default_bit' => Mana_Filters_Resource_Filter2::DM_POSITION,
			'default_label' => Mage::helper('mana_admin')->isGlobal() 
				? ($this->getModel()->getType() != 'category' ? $this->__('Use Attribute Configuration') : $this->__('Use Default'))
				: $this->__('Same For All Stores'),
		));
		$field->setRenderer($this->getLayout()->getBlockSingleton('mana_admin/crud_card_field'));

        if ($form->getModel()->getType() == 'attribute') {
            $fieldset->addField('sort_method', 'select', array(
                'label' => $this->__('Sort Items By'),
                'name' => 'sort_method',
                'options' => Mage::getSingleton('mana_filters/sort')->getOptionArray(),
                'required' => true,
                'default_bit' => Mana_Filters_Resource_Filter2::DM_SORT_METHOD,
                'default_label' => Mage::helper('mana_admin')->isGlobal()
                    ? $this->__('Use System Configuration')
                    : $this->__('Same For All Stores'),
            ))->setRenderer($this->getLayout()->getBlockSingleton('mana_admin/crud_card_field'));

            $fieldset->addField('operation', 'select', array_merge(array(
                'label' => $this->__('Combine Multiple Selections Using'),
                'name' => 'operation',
                'options' => Mage::getSingleton('mana_filters/operation')->getOptionArray(),
                'required' => true,
            ), Mage::helper('mana_admin')->isGlobal() ? array() : array(
                'default_bit' => Mana_Filters_Resource_Filter2::DM_OPERATION,
                'default_label' => $this->__('Same For All Stores'),
            )))->setRenderer($this->getLayout()->getBlockSingleton('mana_admin/crud_card_field'));

        }
        if ($form->getModel()->getType() != 'category') {
            $fieldset->addField('is_reverse', 'select', array_merge(array(
                'label' => $this->__('Reverse Mode'),
                'note' => $this->__('If enabled, shows all items as selected and lets user to deselect items he/she is not interested in'),
                'name' => 'is_reverse',
                'options' => Mage::getSingleton('mana_core/source_yesno')->getOptionArray(),
                'required' => true,
            ), Mage::helper('mana_admin')->isGlobal() ? array() : array(
                'default_bit' => Mana_Filters_Resource_Filter2::DM_IS_REVERSE,
                'default_label' => $this->__('Same For All Stores'),
            )))->setRenderer($this->getLayout()->getBlockSingleton('mana_admin/crud_card_field'));
        }

        $fieldset->addField('disable_no_result_options', 'select', array(
            'label' => $this->__('Filterable (no results) Links Are Not Clickable'),
            'name' => 'disable_no_result_options',
            'options' => Mage::getSingleton('mana_core/source_yesno')->getOptionArray(),
            'required' => true,
            'default_bit' => Mana_Filters_Resource_Filter2::DM_DISABLE_NO_RESULT_OPTIONS,
			'default_label' => Mage::helper('mana_admin')->isGlobal()
				? $this->__('Use System Configuration')
				: $this->__('Same For All Stores'),
        ))->setRenderer($this->getLayout()->getBlockSingleton('mana_admin/crud_card_field'));

        $this->setForm($form);
        return parent::_prepareForm();
	}

	////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// TAB PROPERTIES
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	/**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel() {
    	return $this->__('General');
    }
    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle() {
    	return $this->__('General');
    }
    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab() {
    	return true;
    }
    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden() {
    	return false;
    }
    public function getAjaxUrl() {
    	return Mage::helper('mana_admin')->getStoreUrl('*/*/tabGeneral', 
			array('id' => Mage::app()->getRequest()->getParam('id')), 
			array('ajax' => 1)
		);
    }

    #region Dependencies

    /**
     * @return Mana_Filters_Model_Filter2
     */
    public function getModel() {
        return Mage::registry('m_crud_model');
    }

    /**
     * @return Mana_Admin_Helper_Data
     */
    public function adminHelper() {
        return Mage::helper('mana_admin');
    }

    #endregion
}