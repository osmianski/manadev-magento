<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterColors
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Enter description here ...
 * @author Mana Team
 *
 */
class ManaPro_FilterColors_Block_Header extends Mana_Admin_Block_Crud_Card_Form {
	protected function _prepareForm() {
        $filter = Mage::registry('m_crud_model');

		// form - collection of fieldsets
		$form = new Varien_Data_Form(array(
			'id' => 'mf_colors_header',
			'html_id_prefix' => 'mf_colors_header_',
			'use_container' => true,
			'method' => 'post',
			'action' => $this->getUrl('*/*/save', array('_current' => true)),
			'field_name_suffix' => 'fields',
			'model' => Mage::registry('m_crud_model'),
		));
		Mage::helper('mana_core/js')->options('edit-form', array('subforms' => array('#mf_colors_header' => '#mf_colors_header')));
		
		// fieldset - collection of fields
		$fieldset = $form->addFieldset('mfs_colors', array(
			'title' => $this->__('Colors and Images (Picker)'),
			'legend' => $this->__('Colors and Images (Picker)'),
		));
		$fieldset->setRenderer($this->getLayout()->getBlockSingleton('mana_admin/crud_card_fieldset'));
		
		$field = $fieldset->addField('image_width', 'text', array(
			'label' => $this->__('Width'),
			'name' => 'image_width',
			'required' => true,
			'default_bit' => Mana_Filters_Resource_Filter2::DM_IMAGE_WIDTH,
			'default_label' => Mage::helper('mana_admin')->isGlobal() 
				? $this->__('Use System Configuration') 
				: $this->__('Same For All Stores'),
		));
		$field->setRenderer($this->getLayout()->getBlockSingleton('mana_admin/crud_card_field'));
		
		$field = $fieldset->addField('image_height', 'text', array(
			'label' => $this->__('Height'),
			'name' => 'image_height',
			'required' => true,
			'default_bit' => Mana_Filters_Resource_Filter2::DM_IMAGE_HEIGHT,
			'default_label' => Mage::helper('mana_admin')->isGlobal() 
				? $this->__('Use System Configuration') 
				: $this->__('Same For All Stores'),
		));
		$field->setRenderer($this->getLayout()->getBlockSingleton('mana_admin/crud_card_field'));
		
		$field = $fieldset->addField('image_border_radius', 'text', array(
			'label' => $this->__('Border Radius'),
			'name' => 'image_border_radius',
			'required' => true,
			'default_bit' => Mana_Filters_Resource_Filter2::DM_IMAGE_BORDER_RADIUS,
			'default_label' => Mage::helper('mana_admin')->isGlobal() 
				? $this->__('Use System Configuration') 
				: $this->__('Same For All Stores'),
		));
		$field->setRenderer($this->getLayout()->getBlockSingleton('mana_admin/crud_card_field'));
		
        $field = $fieldset->addField('image_normal', 'text', array_merge(array(
            'label' => $this->__('Image'),
            'name' => 'image_normal',
            'required' => false,
            'image_width' => $filter->getImageWidth(),
            'image_height' => $filter->getImageHeight(),
            'image_border_radius' => $filter->getImageBorderRadius(),
        ), Mage::helper('mana_admin')->isGlobal() ? array() : array(
            'default_bit' => Mana_Filters_Resource_Filter2::DM_IMAGE_NORMAL,
            'default_label' => $this->__('Same For All Stores'),
        )));
        $field->setRenderer($this->getLayout()->getBlockSingleton('manapro_filtercolors/field_image'));

        $field = $fieldset->addField('image_selected', 'text', array_merge(array(
            'label' => $this->__('Selected'),
            'name' => 'image_selected',
            'required' => false,
            'image_width' => $filter->getImageWidth(),
            'image_height' => $filter->getImageHeight(),
            'image_border_radius' => $filter->getImageBorderRadius(),
        ), Mage::helper('mana_admin')->isGlobal() ? array() : array(
            'default_bit' => Mana_Filters_Resource_Filter2::DM_IMAGE_SELECTED,
            'default_label' => $this->__('Same For All Stores'),
        )));
        $field->setRenderer($this->getLayout()->getBlockSingleton('manapro_filtercolors/field_image'));

        $field = $fieldset->addField('image_normal_hovered', 'text', array_merge(array(
            'label' => $this->__('Mouse Over'),
            'name' => 'image_normal_hovered',
            'required' => false,
            'image_width' => $filter->getImageWidth(),
            'image_height' => $filter->getImageHeight(),
            'image_border_radius' => $filter->getImageBorderRadius(),
        ), Mage::helper('mana_admin')->isGlobal() ? array() : array(
            'default_bit' => Mana_Filters_Resource_Filter2::DM_IMAGE_NORMAL_HOVERED,
            'default_label' => $this->__('Same For All Stores'),
        )));
        $field->setRenderer($this->getLayout()->getBlockSingleton('manapro_filtercolors/field_image'));

        $field = $fieldset->addField('image_selected_hovered', 'text', array_merge(array(
            'label' => $this->__('Selected Mouse Over'),
            'name' => 'image_selected_hovered',
            'required' => false,
            'image_width' => $filter->getImageWidth(),
            'image_height' => $filter->getImageHeight(),
            'image_border_radius' => $filter->getImageBorderRadius(),
        ), Mage::helper('mana_admin')->isGlobal() ? array() : array(
            'default_bit' => Mana_Filters_Resource_Filter2::DM_IMAGE_SELECTED_HOVERED,
            'default_label' => $this->__('Same For All Stores'),
        )));
        $field->setRenderer($this->getLayout()->getBlockSingleton('manapro_filtercolors/field_image'));

		// fieldset - collection of fields
		$fieldset = $form->addFieldset('mfs_colors_state', array(
			'title' => $this->__('Colors and Images (State)'),
			'legend' => $this->__('Colors and Images (State)'),
		));
		$fieldset->setRenderer($this->getLayout()->getBlockSingleton('mana_admin/crud_card_fieldset'));

        $field = $fieldset->addField('color_state_display', 'select', array_merge(array(
			'label' => $this->__('Display In State'),
			'name' => 'color_state_display',
			'required' => true,
			'options' => Mage::getSingleton('manapro_filtercolors/source_stateDisplay')->getOptionArray(),
        ), Mage::helper('mana_admin')->isGlobal() ? array() : array(
            'default_bit' => Mana_Filters_Resource_Filter2::DM_COLOR_STATE_DISPLAY,
            'default_label' => $this->__('Same For All Stores'),
        )));
        /** @noinspection PhpParamsInspection */
        $field->setRenderer($this->getLayout()->getBlockSingleton('mana_admin/crud_card_field'));

        $field = $fieldset->addField('state_width', 'text', array(
            'label' => $this->__('Width'),
            'name' => 'state_width',
            'required' => true,
            'default_bit' => Mana_Filters_Resource_Filter2::DM_STATE_WIDTH,
            'default_label' => Mage::helper('mana_admin')->isGlobal()
                ? $this->__('Use System Configuration')
                : $this->__('Same For All Stores'),
        ));
        $field->setRenderer($this->getLayout()->getBlockSingleton('mana_admin/crud_card_field'));

        $field = $fieldset->addField('state_height', 'text', array(
            'label' => $this->__('Height'),
            'name' => 'state_height',
            'required' => true,
            'default_bit' => Mana_Filters_Resource_Filter2::DM_STATE_HEIGHT,
            'default_label' => Mage::helper('mana_admin')->isGlobal()
                ? $this->__('Use System Configuration')
                : $this->__('Same For All Stores'),
        ));
        $field->setRenderer($this->getLayout()->getBlockSingleton('mana_admin/crud_card_field'));

        $field = $fieldset->addField('state_border_radius', 'text', array(
            'label' => $this->__('Border Radius'),
            'name' => 'state_border_radius',
            'required' => true,
            'default_bit' => Mana_Filters_Resource_Filter2::DM_STATE_BORDER_RADIUS,
            'default_label' => Mage::helper('mana_admin')->isGlobal()
                ? $this->__('Use System Configuration')
                : $this->__('Same For All Stores'),
        ));
        $field->setRenderer($this->getLayout()->getBlockSingleton('mana_admin/crud_card_field'));

        $field = $fieldset->addField('state_image', 'text', array_merge(array(
            'label' => $this->__('Image'),
            'name' => 'state_image',
            'required' => false,
            'image_width' => $filter->getStateWidth(),
            'image_height' => $filter->getStateHeight(),
            'image_border_radius' => $filter->getStateBorderRadius(),
        ), Mage::helper('mana_admin')->isGlobal() ? array() : array(
            'default_bit' => Mana_Filters_Resource_Filter2::DM_STATE_IMAGE,
            'default_label' => $this->__('Same For All Stores'),
        )));
        $field->setRenderer($this->getLayout()->getBlockSingleton('manapro_filtercolors/field_image'));

		// result
        $this->setForm($form);
        return parent::_prepareForm();
	}
}