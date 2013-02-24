<?php
/**
 * @category    Mana
 * @package     ManaPro_Guestbook
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Enter description here ...
 * @author Mana Team
 *
 */
class ManaPro_Guestbook_Block_Card_General extends Mana_Admin_Block_Crud_Card_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface {
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
			'model' => Mage::registry('m_crud_model'),
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
        $field = $fieldset->addField('created_at', 'date', array(
            'name'   => 'created_at',
            'required'  => true,
            'label'  => $this->__('Date'),
            'image'  => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'format'       => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
        ));
		$field->setRenderer($this->getLayout()->getBlockSingleton('mana_admin/crud_card_field'));
		
        // get store switcher or a hidden field with its id
        if (!Mage::app()->isSingleStoreMode()) {
            $field = $fieldset->addField('store_id', 'select', array(
                'label'     => $this->__('Store'),
                'name'      => 'store_id',
                'required'  => true,
                'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(),
            ));
        }
        else {
            $field = $fieldset->addField('store_id', 'hidden', array(
                'name'      => 'store_id',
            ));
        }
        $field->setRenderer($this->getLayout()->getBlockSingleton('mana_admin/crud_card_field'));

		$field = $fieldset->addField('status', 'select', array(
			'label' => $this->__('Status'),
			'name' => 'status',
			'required' => true,
			'options' => Mage::getSingleton('manapro_guestbook/source_status')->getOptionArray(),
		));
        $field->setRenderer($this->getLayout()->getBlockSingleton('mana_admin/crud_card_field'));

        // fieldset - collection of fields
        /** @noinspection PhpParamsInspection */
        $fieldset = $form->addFieldset('mfs_fields', array(
            'title' => $this->__('Fields'),
            'legend' => $this->__('Fields'),
        ));
        $fieldset->setRenderer($this->getLayout()->getBlockSingleton('mana_admin/crud_card_fieldset'));

        foreach (Mage::helper('manapro_guestbook')->getVisibleFields() as $field) {
            $method = "_prepareField_$field";
            if ($field = $this->$method($fieldset)) {
                $field->setRenderer($this->getLayout()->getBlockSingleton('mana_admin/crud_card_field'));
            }
        }

		// result
        $this->setForm($form);
        return parent::_prepareForm();
	}

    protected function _prepareField_email($fieldset) {
        if (Mage::getStoreConfigFlag('manapro_guestbook/email/is_enabled')) {
            $field = $fieldset->addField('email', 'text', array(
                'label' => $this->__('Email'),
                'name' => 'email',
            ));
            if (Mage::getStoreConfigFlag('manapro_guestbook/email/is_required')) {
                $field->setRequired(true);
            }
            return $field;
        }
        else {
            return null;
        }
    }
    protected function _prepareField_url($fieldset) {
        if (Mage::getStoreConfigFlag('manapro_guestbook/url/is_enabled')) {
            $field = $fieldset->addField('url', 'text', array(
                'label' => $this->__('Website'),
                'name' => 'url',
            ));
            if (Mage::getStoreConfigFlag('manapro_guestbook/url/is_required')) {
                $field->setRequired(true);
            }
            return $field;
        }
        else {
            return null;
        }
    }
    protected function _prepareField_name($fieldset) {
        if (Mage::getStoreConfigFlag('manapro_guestbook/name/is_enabled')) {
            $field = $fieldset->addField('name', 'text', array(
                'label' => $this->__('Name'),
                'name' => 'name',
            ));
            if (Mage::getStoreConfigFlag('manapro_guestbook/name/is_required')) {
                $field->setRequired(true);
            }
            return $field;
        }
        else {
            return null;
        }
    }
    protected function _prepareField_text($fieldset) {
        if (Mage::getStoreConfigFlag('manapro_guestbook/text/is_enabled')) {
            $field = $fieldset->addField('text', 'textarea', array(
                'label' => $this->__('Text'),
                'name' => 'text',
            ));
            if (Mage::getStoreConfigFlag('manapro_guestbook/text/is_required')) {
                $field->setRequired(true);
            }
            return $field;
        }
        else {
            return null;
        }
    }
    protected function _prepareField_country($fieldset) {
        if (Mage::getStoreConfigFlag('manapro_guestbook/country/is_enabled')) {
            $field = $fieldset->addField('country_id', 'select', array(
                'label' => $this->__('Country'),
                'name' => 'country_id',
                'values' => Mage::getSingleton('directory/country')->getResourceCollection()
                    ->load()->toOptionArray(),
            ));
            if (Mage::getStoreConfigFlag('manapro_guestbook/country/is_required')) {
                $field->setRequired(true);
            }
            return $field;
        }
        else {
            $field = $fieldset->addField('country_id', 'hidden', array(
                'name'      => 'country_id',
            ));
        }
    }
    protected function _prepareField_region($fieldset) {
        if (Mage::getStoreConfigFlag('manapro_guestbook/region/is_enabled')) {
            if (Mage::getStoreConfigFlag('manapro_guestbook/region/is_freeform')) {
                $field = $fieldset->addField('region', 'text', array(
                    'label' => $this->__('State/Province'),
                    'region' => 'text',
                    'name' => 'region',
                ));
            }
            else {
                $field = $fieldset->addField('region_id', 'select', array(
                    'label' => $this->__('State/Province'),
                    'name' => 'region_id',
                ));
            }
            if (Mage::getStoreConfigFlag('manapro_guestbook/region/is_required')) {
                $field->setRequired(true);
            }
            return $field;
        }
        else {
            return null;
        }
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
}