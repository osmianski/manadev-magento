<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterSuperSlider
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * This class observes certain (defined in etc/config.xml) events in the whole system and provides public methods - 
 * handlers for these events.
 * @author Mana Team
 *
 */
class ManaPro_FilterSuperSlider_Model_Observer extends Mage_Core_Helper_Abstract {
    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "m_crud_form")
     * @param Varien_Event_Observer $observer
     */
    public function addFields($observer) {
        /* @var $formBlock Mana_Admin_Block_Crud_Card_Form */ /** @noinspection PhpUndefinedMethodInspection */
        $formBlock = $observer->getEvent()->getForm();
        $form = $formBlock->getForm();
        /* @var $layout Mage_Core_Model_Layout */ $layout = Mage::getSingleton(strtolower('Core/Layout'));
        /* @var $fieldsetRenderer Mana_Admin_Block_Crud_Card_Fieldset */ $fieldsetRenderer =
            $layout->getBlockSingleton(strtolower('Mana_Admin/Crud_Card_Fieldset'));
        /* @var $fieldRenderer Mana_Admin_Block_Crud_Card_Field */ $fieldRenderer =
            $layout->getBlockSingleton(strtolower('Mana_Admin/Crud_Card_Field'));
        /* @var $admin Mana_Admin_Helper_Data */ $admin = Mage::helper(strtolower('Mana_Admin'));
        /* @var $model Mana_Filters_Model_Filter2 */
        /** @noinspection PhpUndefinedMethodInspection */
        $model = $form->getModel();

        switch ($formBlock->getEntityName()) {
            case 'mana_filters/filter2':
            case 'mana_filters/filter2_store':
                if ($form->getId() == 'mf_general') {
					$field = $form->getElement('mfs_display')->addField('min_max_slider_role', 'select', array_merge(array(
						'label' => Mage::helper('manapro_filtersuperslider')->__('Role in Min/Max Slider'),
						'note' => Mage::helper('manapro_filtersuperslider')->__("Min/Max Slider displays two attributes at once, so assign 'Minimum Value' role to one filter , 'Maximum Value' role to other filter."),
						'name' => 'min_max_slider_role',
                        'required' => true,
                        'options' => Mage::getSingleton('manapro_filtersuperslider/source_minMaxRole')->getOptionArray(),
					), $admin->isGlobal() ? array() : array(
                        'default_bit' => Mana_Filters_Resource_Filter2::DM_MIN_MAX_SLIDER_ROLE,
                        'default_label' => Mage::helper('manapro_filtersuperslider')->__('Same For All Stores'),
					)), 'display');
					$field->setRenderer(Mage::getSingleton('core/layout')->getBlockSingleton('mana_admin/crud_card_field'));

					$field = $form->getElement('mfs_display')->addField('min_slider_code', 'select', array_merge(array(
						'label' => Mage::helper('manapro_filtersuperslider')->__('Minimum Value Attribute'),
						'name' => 'min_slider_code',
                        'required' => true,
                        'options' => Mage::getSingleton('manapro_filtersuperslider/source_minSlider')->getOptionArray(),
					), $admin->isGlobal() ? array() : array(
                        'default_bit' => Mana_Filters_Resource_Filter2::DM_MIN_SLIDER_CODE,
                        'default_label' => Mage::helper('manapro_filtersuperslider')->__('Same For All Stores'),
					)), 'min_max_slider_role');
					$field->setRenderer(Mage::getSingleton('core/layout')->getBlockSingleton('mana_admin/crud_card_field'));

                    // fieldset - collection of fields
                    /** @noinspection PhpParamsInspection */
                    $fieldset = $form->addFieldset('mfs_slider', array(
                        'title' => $this->__('Decimal Value Formatting'),
                        'legend' => $this->__('Decimal Value Formatting'),
                        'fieldset_container_id' => 'mfs_slider',
                        'fieldset_container_class' => in_array($model->getType(), array('price', 'decimal')) ? '' : 'm-hidden',
                    ), 'mfs_display')->setRenderer($fieldsetRenderer);

                    $fieldset->addField('slider_number_format', 'text', array(
                        'label' => Mage::helper('manapro_filtersuperslider')->__('Number Format'),
                        'note' => Mage::helper('manapro_filtersuperslider')->__('0 will be replaced by actual number; all other characters will be displayed as entered.'),
                        'name' => 'slider_number_format',
                        'required' => true,
                        'default_bit' => Mana_Filters_Resource_Filter2::DM_SLIDER_NUMBER_FORMAT,
                        'default_label' => $admin->isGlobal()
                            ? Mage::helper('manapro_filtersuperslider')->__('Use Price Format')
                            : Mage::helper('manapro_filtersuperslider')->__('Same For All Stores'),
                    ))->setRenderer($fieldRenderer);
                    $fieldset->addField('slider_decimal_digits', 'text', array_merge(array(
                        'label' => Mage::helper('manapro_filtersuperslider')->__('Digits After the Decimal Point'),
                        'note' => Mage::helper('manapro_filtersuperslider')->__('Leave empty or 0 to round to whole numbers.'),
                        'name' => 'slider_decimal_digits',
                        'required' => true,
                    ), $admin->isGlobal() ? array() : array(
                        'default_bit' => Mana_Filters_Resource_Filter2::DM_SLIDER_DECIMAL_DIGITS,
                        'default_label' => Mage::helper('manapro_filtersuperslider')->__('Same For All Stores'),
                    )))->setRenderer($fieldRenderer);
                    $fieldset->addField('slider_threshold', 'text', array_merge(array(
                        'label' => Mage::helper('manapro_filtersuperslider')->__('Use Second Number Format If Value Is Greater Than'),
                        'note' => Mage::helper('manapro_filtersuperslider')->__('Leave empty or 0 to use basic Number Format above for all values.'),
                        'name' => 'slider_threshold',
                        'required' => false,
                    ), $admin->isGlobal() ? array() : array(
                        'default_bit' => Mana_Filters_Resource_Filter2::DM_SLIDER_THRESHOLD,
                        'default_label' => Mage::helper('manapro_filtersuperslider')->__('Same For All Stores'),
                    )))->setRenderer($fieldRenderer);
                    $fieldset->addField('slider_number_format2', 'text', array_merge(array(
                        'label' => Mage::helper('manapro_filtersuperslider')->__('Second Number Format'),
                        'note' => Mage::helper('manapro_filtersuperslider')->__('Only applicable for values greater than parameter specified above. 0 will be replaced by actual number divided by the parameter specified above; all other characters will be displayed as entered.'),
                        'name' => 'slider_number_format2',
                        'required' => true,
                    ), $admin->isGlobal() ? array() : array(
                        'default_bit' => Mana_Filters_Resource_Filter2::DM_SLIDER_NUMBER_FORMAT2,
                        'default_label' => Mage::helper('manapro_filtersuperslider')->__('Same For All Stores'),
                    )))->setRenderer($fieldRenderer);
                    $fieldset->addField('slider_decimal_digits2', 'text', array_merge(array(
                        'label' => Mage::helper('manapro_filtersuperslider')->__('Digits After the Decimal Point (for Second Format)'),
                        'note' => Mage::helper('manapro_filtersuperslider')->__('Leave empty or 0 to round to whole numbers.'),
                        'name' => 'slider_decimal_digits2',
                        'required' => true,
                    ), $admin->isGlobal() ? array() : array(
                        'default_bit' => Mana_Filters_Resource_Filter2::DM_SLIDER_DECIMAL_DIGITS2,
                        'default_label' => Mage::helper('manapro_filtersuperslider')->__('Same For All Stores'),
                    )))->setRenderer($fieldRenderer);
                    /** @noinspection PhpUndefinedMethodInspection */
                    $fieldset->addField('slider_manual_entry', 'select', array_merge(array(
                        'label' => Mage::helper('manapro_filtersuperslider')->__('Allow Customers to Enter Range Manually'),
                        'name' => 'slider_manual_entry',
                        'required' => true,
                        'options' => Mage::getSingleton('mana_core/source_yesno')->getOptionArray(),
                    ), $admin->isGlobal() ? array() : array(
                        'default_bit' => Mana_Filters_Resource_Filter2::DM_SLIDER_MANUAL_ENTRY,
                        'default_label' => Mage::helper('manapro_filtersuperslider')->__('Same For All Stores'),
                    )))->setRenderer($fieldRenderer);
                    /** @noinspection PhpUndefinedMethodInspection */
                    $fieldset->addField('slider_use_existing_values', 'select', array_merge(array(
                        'label' => Mage::helper('manapro_filtersuperslider')->__('Slide Only Through Existing Values'),
                        'name' => 'slider_use_existing_values',
                        'required' => true,
                        'options' => Mage::getSingleton('mana_core/source_yesno')->getOptionArray(),
                    ), $admin->isGlobal() ? array() : array(
                        'default_bit' => Mana_Filters_Resource_Filter2::DM_SLIDER_USE_EXISTING_VALUES,
                        'default_label' => Mage::helper('manapro_filtersuperslider')->__('Same For All Stores'),
                    )))->setRenderer($fieldRenderer);
                    /** @noinspection PhpUndefinedMethodInspection */
                    $fieldset->addField('thousand_separator', 'select', array_merge(array(
                        'label' => Mage::helper('manapro_filtersuperslider')->__('Show Thousand Separator'),
                        'name' => 'thousand_separator',
                        'required' => true,
                        'options' => Mage::getSingleton('mana_core/source_yesno')->getOptionArray(),
                    ), $admin->isGlobal() ? array() : array(
                        'default_bit' => Mana_Filters_Resource_Filter2::DM_THOUSAND_SEPARATOR,
                        'default_label' => Mage::helper('manapro_filtersuperslider')->__('Same For All Stores'),
                    )))->setRenderer($fieldRenderer);

                    $fieldset = $form->addFieldset('mfs_range', array(
                        'title' => $this->__('Range Settings'),
                        'legend' => $this->__('Range Settings'),
                        'fieldset_container_id' => 'mfs_range',
                        'fieldset_container_class' =>
                            (in_array($model->getType(), array('price', 'decimal')) && $model->getDisplay() != 'slider' ? '' : 'm-hidden').
                            (in_array($model->getType(), array('price', 'decimal')) ? ' m-decimal' : ''),
                    ), 'mfs_slider')->setRenderer($fieldsetRenderer);

                    $fieldset->addField('range_step', 'text', array_merge(array(
                        'label' => Mage::helper('manapro_filtersuperslider')->__('Range Step'),
                        'note' => Mage::helper('manapro_filtersuperslider')->__('When showing filter options, system will divide the whole range of values to segments of specified size. Leave empty or 0 for automatic step calculation.'),
                        'name' => 'range_step',
                        'required' => true,
                        'default_bit' => Mana_Filters_Resource_Filter2::DM_RANGE_STEP,
                        'default_label' => $admin->isGlobal()
                                ? $this->__('Use Standard Behavior')
                                : $this->__('Same For All Stores'),
                    )))->setRenderer($fieldRenderer);

                }
                break;
        }
    }
	/**
	 * Adds columns to replication update select (handles event "m_db_update_columns")
	 * @param Varien_Event_Observer $observer
	 */
	public function prepareUpdateColumns($observer) {
		/* @var $target Mana_Db_Model_Replication_Target */ $target = $observer->getEvent()->getTarget();
		/* @var $options array */ $options = $observer->getEvent()->getOptions();

		switch ($target->getEntityName()) {
			case 'mana_filters/filter2_store':
				$target->getSelect('main')->columns(array(
					'global.slider_number_format AS slider_number_format',
                    'global.slider_manual_entry AS slider_manual_entry',
                    'global.slider_number_format2 AS slider_number_format2',
                    'global.slider_threshold AS slider_threshold',
                    'global.slider_use_existing_values AS slider_use_existing_values',
                    'global.slider_decimal_digits AS slider_decimal_digits',
                    'global.slider_decimal_digits2 AS slider_decimal_digits2',
                    'global.range_step AS range_step',
                    'global.thousand_separator AS thousand_separator',
                    'global.min_max_slider_role AS min_max_slider_role',
                    'global.min_slider_code AS min_slider_code',
                ));
				break;
		}
	}
	/**
	 * Adds values to be updated (handles event "m_db_update_process")
	 * @param Varien_Event_Observer $observer
	 */
	public function processUpdate($observer) {
		/* @var $object Mana_Db_Model_Object */ $object = $observer->getEvent()->getObject();
		/* @var $values array */ $values = $observer->getEvent()->getValues();
		/* @var $options array */ $options = $observer->getEvent()->getOptions();

		switch ($object->getEntityName()) {
			case 'mana_filters/filter2':
				if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_SLIDER_NUMBER_FORMAT)) {
					$object->setSliderNumberFormat($this->_getDefaultSliderNumberFormat());
				}
                if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_RANGE_STEP)) {
                    $object->setRangeStep($this->_getDefaultRangeStep($object));
                }
                break;
			case 'mana_filters/filter2_store':
				if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_SLIDER_NUMBER_FORMAT)) {
					$object->setSliderNumberFormat($values['slider_number_format']);
				}
                if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_SLIDER_MANUAL_ENTRY)) {
                    $object->setSliderManualEntry($values['slider_manual_entry']);
                }
                if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_SLIDER_NUMBER_FORMAT2)) {
                    $object->setSliderNumberFormat2($values['slider_number_format2']);
                }
                if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_SLIDER_THRESHOLD)) {
                    $object->setSliderThreshold($values['slider_threshold']);
                }
                if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_SLIDER_USE_EXISTING_VALUES)) {
                    $object->setSliderUseExistingValues($values['slider_use_existing_values']);
                }
                if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_SLIDER_DECIMAL_DIGITS)) {
                    $object->setSliderDecimalDigits($values['slider_decimal_digits']);
                }
                if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_SLIDER_DECIMAL_DIGITS2)) {
                    $object->setSliderDecimalDigits2($values['slider_decimal_digits2']);
                }
                if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_RANGE_STEP)) {
                    $object->setRangeStep($values['range_step']);
                }
                if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_THOUSAND_SEPARATOR)) {
                    $object->setThousandSeparator($values['thousand_separator']);
                }
                if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_MIN_MAX_SLIDER_ROLE)) {
                    $object->setData('min_max_slider_role', $values['min_max_slider_role']);
                }
                if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_MIN_SLIDER_CODE)) {
                    $object->setData('min_slider_code', $values['min_slider_code']);
                }
                break;
		}
	}
	/**
	 * Adds columns to replication insert select (handles event "m_db_insert_columns")
	 * @param Varien_Event_Observer $observer
	 */
	public function prepareInsertColumns($observer) {
		/* @var $target Mana_Db_Model_Replication_Target */ $target = $observer->getEvent()->getTarget();
		/* @var $options array */ $options = $observer->getEvent()->getOptions();

		switch ($target->getEntityName()) {
			case 'mana_filters/filter2_store':
				$target->getSelect('main')->columns(array(
                    'global.slider_number_format AS slider_number_format',
                    'global.slider_manual_entry AS slider_manual_entry',
                    'global.slider_number_format2 AS slider_number_format2',
                    'global.slider_threshold AS slider_threshold',
                    'global.slider_use_existing_values AS slider_use_existing_values',
                    'global.slider_decimal_digits AS slider_decimal_digits',
                    'global.slider_decimal_digits2 AS slider_decimal_digits2',
                    'global.range_step AS range_step',
                    'global.thousand_separator AS thousand_separator',
                    'global.min_max_slider_role AS min_max_slider_role',
                    'global.min_slider_code AS min_slider_code',
                ));
				break;
		}
	}
	/**
	 * Adds values to be inserted (handles event "m_db_insert_process")
	 * @param Varien_Event_Observer $observer
	 */
	public function processInsert($observer) {
		/* @var $object Mana_Db_Model_Object */ $object = $observer->getEvent()->getObject();
		/* @var $values array */ $values = $observer->getEvent()->getValues();
		/* @var $options array */ $options = $observer->getEvent()->getOptions();

		switch ($object->getEntityName()) {
			case 'mana_filters/filter2':
                $object->setSliderNumberFormat($this->_getDefaultSliderNumberFormat());
                $object->setRangeStep($this->_getDefaultRangeStep($object));
                break;
			case 'mana_filters/filter2_store':
                $object->setSliderNumberFormat($values['slider_number_format']);
                $object->setSliderManualEntry($values['slider_manual_entry']);
                $object->setSliderNumberFormat2($values['slider_number_format2']);
                $object->setSliderThreshold($values['slider_threshold']);
                $object->setSliderUseExistingValues($values['slider_use_existing_values']);
                $object->setSliderDecimalDigits($values['slider_decimal_digits']);
                $object->setSliderDecimalDigits2($values['slider_decimal_digits2']);
                $object->setRangeStep($values['range_step']);
                $object->setThousandSeparator($values['thousand_separator']);
                $object->setData('min_max_slider_role', $values['min_max_slider_role']);
                $object->setData('min_slider_code', $values['min_slider_code']);
                break;
		}
	}

    /**
     * Adds edited data received via HTTP to specified model (handles event "m_db_add_edited_data")
     * @param Varien_Event_Observer $observer
     */
    public function addEditedData($observer) {
        /* @var $object Mana_Db_Model_Object */ $object = $observer->getEvent()->getObject();
        /* @var $fields array */ $fields = $observer->getEvent()->getFields();
        /* @var $useDefault array */ $useDefault = $observer->getEvent()->getUseDefault();

        switch ($object->getEntityName()) {
            case 'mana_filters/filter2':
            case 'mana_filters/filter2_store':
                Mage::helper('mana_db')->updateDefaultableField($object, 'slider_number_format', Mana_Filters_Resource_Filter2::DM_SLIDER_NUMBER_FORMAT, $fields, $useDefault);
                Mage::helper('mana_db')->updateDefaultableField($object, 'slider_manual_entry', Mana_Filters_Resource_Filter2::DM_SLIDER_MANUAL_ENTRY, $fields, $useDefault);
                Mage::helper('mana_db')->updateDefaultableField($object, 'slider_number_format2', Mana_Filters_Resource_Filter2::DM_SLIDER_NUMBER_FORMAT2, $fields, $useDefault);
                Mage::helper('mana_db')->updateDefaultableField($object, 'slider_threshold', Mana_Filters_Resource_Filter2::DM_SLIDER_THRESHOLD, $fields, $useDefault);
                Mage::helper('mana_db')->updateDefaultableField($object, 'slider_use_existing_values', Mana_Filters_Resource_Filter2::DM_SLIDER_USE_EXISTING_VALUES, $fields, $useDefault);
                Mage::helper('mana_db')->updateDefaultableField($object, 'slider_decimal_digits', Mana_Filters_Resource_Filter2::DM_SLIDER_DECIMAL_DIGITS, $fields, $useDefault);
                Mage::helper('mana_db')->updateDefaultableField($object, 'slider_decimal_digits2', Mana_Filters_Resource_Filter2::DM_SLIDER_DECIMAL_DIGITS2, $fields, $useDefault);
                Mage::helper('mana_db')->updateDefaultableField($object, 'range_step', Mana_Filters_Resource_Filter2::DM_RANGE_STEP, $fields, $useDefault);
                Mage::helper('mana_db')->updateDefaultableField($object, 'thousand_separator', Mana_Filters_Resource_Filter2::DM_THOUSAND_SEPARATOR, $fields, $useDefault);
                Mage::helper('mana_db')->updateDefaultableField($object, 'min_max_slider_role', Mana_Filters_Resource_Filter2::DM_MIN_MAX_SLIDER_ROLE, $fields, $useDefault);
                Mage::helper('mana_db')->updateDefaultableField($object, 'min_slider_code', Mana_Filters_Resource_Filter2::DM_MIN_SLIDER_CODE, $fields, $useDefault);
                break;
        }
    }
    /* BASED ON SNIPPET: Models/Event handler */
    /**
     * Validates edited data (handles event "m_db_validate")
     * @param Varien_Event_Observer $observer
     */
    public function validate($observer) {
        /* @var $object Mana_Db_Model_Object */ $object = $observer->getEvent()->getObject();
        /* @var $result Mana_Db_Model_Validation */ $result = $observer->getEvent()->getResult();

        switch ($object->getEntityName()) {
            case 'mana_filters/filter2':
            case 'mana_filters/filter2_store':
                $t = Mage::helper('manapro_filtersuperslider');
                if (trim($object->getSliderNumberFormat()) === '') {
                    $result->addError($t->__('Please fill in %s field', $t->__('Number Format')));
                }
                if (substr_count($object->getSliderNumberFormat(), '0') != 1) {
                    $result->addError($t->__('%s must have exactly one 0', $t->__('Number Format')));
                }
                if ($object->getSliderThreshold() && !is_numeric($object->getSliderThreshold())) {
                    $result->addError($t->__('%s is not a number', $t->__('Use Second Number Format If Value Is Greater Than')));
                }
                if ($object->getSliderThreshold()) {
                    if (trim($object->getSliderNumberFormat2()) === '') {
                        $result->addError($t->__('Please fill in %s field', $t->__('Second Number Format')));
                    }
                    if (substr_count($object->getSliderNumberFormat2(), '0') != 1) {
                        $result->addError($t->__('%s must have exactly one 0', $t->__('Second Number Format')));
                    }
                }
                if (trim($object->getSliderManualEntry()) === '') {
                    $result->addError($t->__('Please fill in %s field', $t->__('Allow Customers to Enter Range Manually')));
                }
                if (trim($object->getSliderUseExistingValues()) === '') {
                    $result->addError($t->__('Please fill in %s field', $t->__('Slide Only Through Existing Values')));
                }
                if (trim($object->getSliderDecimalDigits()) === '') {
                    $result->addError($t->__('Please fill in %s field', $t->__('Digits After the Decimal Point')));
                }
                if (trim($object->getSliderDecimalDigits2()) === '') {
                    $result->addError($t->__('Please fill in %s field', $t->__('Digits After the Decimal Point (for Second Format)')));
                }
                if ($object->getRangeStep() && !is_numeric($object->getRangeStep())) {
                    $result->addError($t->__('%s is not a number', $t->__('Range Step')));
                }
                if ($object->getDisplay() == 'min_max_slider' && !$object->getMinMaxSliderRole()) {
                    $result->addError($t->__('Please fill in %s field', $t->__('Role in Min/Max Slider')));
                }
                if ($object->getDisplay() == 'min_max_slider' && $object->getMinMaxSliderRole() == 'max' && !$object->getMinSliderCode()) {
                    $result->addError($t->__('Please fill in %s field', $t->__('Minimum Value Attribute')));
                }
                break;
        }
    }
    protected function _getDefaultSliderNumberFormat() {
        ///* @var $helper Mana_Filters_Helper_Data */ $helper = Mage::helper(strtolower('Mana_Filters'));
        //return $helper->getJsPriceFormat();
        return '$0';
    }
    protected function _getDefaultRangeStep($object) {
        if ($object->getCode() == 'price') {
            return Mage::helper('mana_db')->getLatestConfig('catalog/layered_navigation/price_range_calculation') == 'auto' ? 0 :
                    Mage::helper('mana_db')->getLatestConfig('catalog/layered_navigation/price_range_step');
        }
        else {
            return 0;
        }
    }
    /* BASED ON SNIPPET: Models/Event handler */
    /**
     * Raises flag is config value changed this module's replicated tables rely on (handles event "m_db_is_config_changed")
     * @param Varien_Event_Observer $observer
     */
    public function isConfigChanged($observer) {
        /* @var $result Varien_Object */
        $result = $observer->getEvent()->getResult();
        /* @var $configData Mage_Core_Model_Config_Data */
        $configData = $observer->getEvent()->getConfigData();

        Mage::helper('mana_db')->checkIfPathsChanged($result, $configData, array(
            'catalog/layered_navigation/price_range_calculation',
            'catalog/layered_navigation/price_range_step',
            ));
    }
    /**
     * Applies specific formatting to price range (handles event "m_render_price_range")
     * @param Varien_Event_Observer $observer
     */
    public function renderPriceRange($observer) {
        /* @var $range array */ $range = $observer->getEvent()->getRange();
        /* @var $model Mana_Filters_Model_Filter_Decimal */ $model = $observer->getEvent()->getModel();
        /* @var $result Varien_Object */ $result = $observer->getEvent()->getResult();

        /* @var $helper ManaPro_FilterSuperSlider_Helper_Data */
        $helper = Mage::helper(strtolower('ManaPro_FilterSuperSlider'));
        $fromPrice = $helper->formatNumber($range['from'], $model->getFilterOptions());
        $toPrice = $helper->formatNumber($range['to'], $model->getFilterOptions());
        $result->setLabel(Mage::helper('catalog')->__('%s - %s', $fromPrice, $toPrice));
    }
    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "m_before_load_filter_collection")
     * @param Varien_Event_Observer $observer
     */
    public function addRangeStepToCollection($observer) {
        /* @var $collection Mana_Filters_Resource_Filter2_Store_Collection */ $collection = $observer->getEvent()->getCollection();
        $collection->addGlobalFields(array('range_step'));
    		
        // INSERT HERE: event handler code
    }
    
    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "m_advanced_filter_menu_container_css")
     * @param Varien_Event_Observer $observer
     */
    public function renderMenuContainerCss($observer) {
        /* @var $block Mana_Filters_Block_View */ $block = $observer->getEvent()->getBlock();
        /* @var $filter Mana_Filters_Block_Filter */ $filter = $observer->getEvent()->getFilter();
    		
        if (in_array($filter->getFilterOptions()->getDisplay(), array('slider', 'range', 'min_max_slider'))) {
            if (Mage::getStoreConfig('mana_filters/positioning_menu/inline_slider') == 'inline') {
                echo ' m-inline';
            }
        }
    }
    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "m_advanced_filter_menu_visible")
     * @param Varien_Event_Observer $observer
     */
    public function renderMenuVisible($observer) {
        /* @var $block Mana_Filters_Block_View */ $block = $observer->getEvent()->getBlock();
        /* @var $filter Mana_Filters_Block_Filter */ $filter = $observer->getEvent()->getFilter();
        /* @var $result Varien_Object */ $result = $observer->getEvent()->getResult();

        if (in_array($filter->getFilterOptions()->getDisplay(), array('slider', 'range', 'min_max_slider'))) {
            if (Mage::getStoreConfig('mana_filters/positioning_menu/inline_slider') == 'inline') {
                $result->setResult(true);
            }
        }
    }
}