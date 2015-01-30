<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterColors
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/* BASED ON SNIPPET: Models/Observer */
/**
 * This class observes certain (defined in etc/config.xml) events in the whole system and provides public methods - handlers for
 * these events.
 * @author Mana Team
 *
 */
class ManaPro_FilterColors_Model_Observer {
	/* BASED ON SNIPPET: Models/Event handler */
	/**
	 * Raises flag is config value changed this module's replicated tables rely on (handles event "m_db_is_config_changed")
	 * @param Varien_Event_Observer $observer
	 */
	public function isConfigChanged($observer) {
		/* @var $result Varien_Object */ $result = $observer->getEvent()->getResult();
		/* @var $configData Mage_Core_Model_Config_Data */ $configData = $observer->getEvent()->getConfigData();
		
		Mage::helper('mana_db')->checkIfPathsChanged($result, $configData, array(
			'mana_filters/colors/image_width',
			'mana_filters/colors/image_height',
			'mana_filters/colors/image_border_radius',
            'mana_filters/colors/state_width',
            'mana_filters/colors/state_height',
            'mana_filters/colors/state_border_radius',
		));
	}
	/* BASED ON SNIPPET: Models/Event handler */
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
					'global.image_width AS image_width',
					'global.image_height AS image_height',
					'global.image_border_radius AS image_border_radius',
                    'global.image_normal AS image_normal',
                    'global.image_selected AS image_selected',
                    'global.image_normal_hovered AS image_normal_hovered',
                    'global.image_selected_hovered AS image_selected_hovered',
                    'global.state_width AS state_width',
                    'global.state_height AS state_height',
                    'global.state_border_radius AS state_border_radius',
                    'global.state_image AS state_image',
                    'global.color_state_display AS color_state_display',
				));
				break;
			case 'mana_filters/filter2_value_store':
				$target->getSelect('main')->columns(array(
					'global.color AS color',
					'global.normal_image AS normal_image',
					'global.selected_image AS selected_image',
					'global.normal_hovered_image AS normal_hovered_image',
					'global.selected_hovered_image AS selected_hovered_image',
                    'global.state_image AS state_image',
				));
				break;
		}
	}
	/* BASED ON SNIPPET: Models/Event handler */
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
				if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_IMAGE_WIDTH)) {
					$object->setImageWidth(Mage::helper('mana_db')->getLatestConfig('mana_filters/colors/image_width'));
				}
				if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_IMAGE_HEIGHT)) {
					$object->setImageHeight(Mage::helper('mana_db')->getLatestConfig('mana_filters/colors/image_height'));
				}
				if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_IMAGE_BORDER_RADIUS)) {
					$object->setImageBorderRadius(Mage::helper('mana_db')->getLatestConfig('mana_filters/colors/image_border_radius'));
				}
                if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_STATE_WIDTH)) {
                    $object->setStateWidth(Mage::helper('mana_db')->getLatestConfig('mana_filters/colors/state_width'));
                }
                if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_STATE_HEIGHT)) {
                    $object->setStateHeight(Mage::helper('mana_db')->getLatestConfig('mana_filters/colors/state_height'));
                }
                if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_STATE_BORDER_RADIUS)) {
                    $object->setStateBorderRadius(Mage::helper('mana_db')->getLatestConfig('mana_filters/colors/state_border_radius'));
                }
				break;
			case 'mana_filters/filter2_store':
				if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_IMAGE_WIDTH)) {
					$object->setImageWidth($values['image_width']);
				}
				if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_IMAGE_HEIGHT)) {
					$object->setImageHeight($values['image_height']);
				}
				if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_IMAGE_BORDER_RADIUS)) {
					$object->setImageBorderRadius($values['image_border_radius']);
				}
                if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_IMAGE_NORMAL)) {
                    $object->setImageNormal($values['image_normal']);
                }
                if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_IMAGE_SELECTED)) {
                    $object->setImageSelected($values['image_selected']);
                }
                if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_IMAGE_NORMAL_HOVERED)) {
                    $object->setImageNormalHovered($values['image_normal_hovered']);
                }
                if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_IMAGE_SELECTED_HOVERED)) {
                    $object->setImageSelectedHovered($values['image_selected_hovered']);
                }
                if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_STATE_WIDTH)) {
                    $object->setStateWidth($values['state_width']);
                }
                if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_STATE_HEIGHT)) {
                    $object->setStateHeight($values['state_height']);
                }
                if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_STATE_BORDER_RADIUS)) {
                    $object->setStateBorderRadius($values['state_border_radius']);
                }
                if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_STATE_IMAGE)) {
                    $object->setStateImage($values['state_image']);
                }
                if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_COLOR_STATE_DISPLAY)) {
                    $object->setData('color_state_display', $values['color_state_display']);
                }
				break;
			case 'mana_filters/filter2_value_store':
				if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2_Value::DM_COLOR)) {
					$object->setColor($values['color']);
				}
				if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2_Value::DM_NORMAL_IMAGE)) {
					$object->setNormalImage($values['normal_image']);
				}
				if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2_Value::DM_SELECTED_IMAGE)) {
					$object->setSelectedImage($values['selected_image']);
				}
				if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2_Value::DM_NORMAL_HOVERED_IMAGE)) {
					$object->setNormalHoveredImage($values['normal_hovered_image']);
				}
				if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2_Value::DM_SELECTED_HOVERED_IMAGE)) {
					$object->setSelectedHoveredImage($values['selected_hovered_image']);
				}
                if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2_Value::DM_STATE_IMAGE)) {
                    $object->setStateImage($values['state_image']);
                }
				break;
		}
	}
	/* BASED ON SNIPPET: Models/Event handler */
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
					'global.image_width AS image_width',
					'global.image_height AS image_height',
					'global.image_border_radius AS image_border_radius',
                    'global.image_normal AS image_normal',
                    'global.image_selected AS image_selected',
                    'global.image_normal_hovered AS image_normal_hovered',
                    'global.image_selected_hovered AS image_selected_hovered',
                    'global.state_width AS state_width',
                    'global.state_height AS state_height',
                    'global.state_border_radius AS state_border_radius',
                    'global.state_image AS state_image',
                    'global.color_state_display AS color_state_display',
				));
				break;
			case 'mana_filters/filter2_value_store':
				$target->getSelect('main')->columns(array(
					'global.color AS color',
					'global.normal_image AS normal_image',
					'global.selected_image AS selected_image',
					'global.normal_hovered_image AS normal_hovered_image',
					'global.selected_hovered_image AS selected_hovered_image',
                    'global.state_image AS state_image',
				));
				break;
		}
	}
	/* BASED ON SNIPPET: Models/Event handler */
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
                $object->setImageWidth(Mage::helper('mana_db')->getLatestConfig('mana_filters/colors/image_width'));
                $object->setImageHeight(Mage::helper('mana_db')->getLatestConfig('mana_filters/colors/image_height'));
                $object->setImageBorderRadius(Mage::helper('mana_db')->getLatestConfig('mana_filters/colors/image_border_radius'));
                $object->setStateWidth(Mage::helper('mana_db')->getLatestConfig('mana_filters/colors/state_width'));
                $object->setStateHeight(Mage::helper('mana_db')->getLatestConfig('mana_filters/colors/state_height'));
                $object->setStateBorderRadius(Mage::helper('mana_db')->getLatestConfig('mana_filters/colors/state_border_radius'));
                break;
            case 'mana_filters/filter2_store':
                $object->setImageWidth($values['image_width']);
                $object->setImageHeight($values['image_height']);
                $object->setImageBorderRadius($values['image_border_radius']);
                $object->setImageNormal($values['image_normal']);
                $object->setImageSelected($values['image_selected']);
                $object->setImageNormalHovered($values['image_normal_hovered']);
                $object->setImageSelectedHovered($values['image_selected_hovered']);
                $object->setStateWidth($values['state_width']);
                $object->setStateHeight($values['state_height']);
                $object->setStateBorderRadius($values['state_border_radius']);
                $object->setStateImage($values['state_image']);
                $object->setData('color_state_display', $values['color_state_display']);
                break;
            case 'mana_filters/filter2_value_store':
                $object->setColor($values['color']);
                $object->setNormalImage($values['normal_image']);
                $object->setSelectedImage($values['selected_image']);
                $object->setNormalHoveredImage($values['normal_hovered_image']);
                $object->setSelectedHoveredImage($values['selected_hovered_image']);
                $object->setStateImage($values['state_image']);
                break;
		}
	}
	/* BASED ON SNIPPET: Models/Event handler */
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
				Mage::helper('mana_db')->updateDefaultableField($object, 'image_width', Mana_Filters_Resource_Filter2::DM_IMAGE_WIDTH, $fields, $useDefault);
				Mage::helper('mana_db')->updateDefaultableField($object, 'image_height', Mana_Filters_Resource_Filter2::DM_IMAGE_HEIGHT, $fields, $useDefault);
				Mage::helper('mana_db')->updateDefaultableField($object, 'image_border_radius', Mana_Filters_Resource_Filter2::DM_IMAGE_BORDER_RADIUS, $fields, $useDefault);
                Mage::helper('mana_db')->updateDefaultableField($object, 'image_normal', Mana_Filters_Resource_Filter2::DM_IMAGE_NORMAL, $fields, $useDefault);
                Mage::helper('mana_db')->updateDefaultableField($object, 'image_selected', Mana_Filters_Resource_Filter2::DM_IMAGE_SELECTED, $fields, $useDefault);
                Mage::helper('mana_db')->updateDefaultableField($object, 'image_normal_hovered', Mana_Filters_Resource_Filter2::DM_IMAGE_NORMAL_HOVERED, $fields, $useDefault);
                Mage::helper('mana_db')->updateDefaultableField($object, 'image_selected_hovered', Mana_Filters_Resource_Filter2::DM_IMAGE_SELECTED_HOVERED, $fields, $useDefault);
                Mage::helper('mana_db')->updateDefaultableField($object, 'state_width', Mana_Filters_Resource_Filter2::DM_STATE_WIDTH, $fields, $useDefault);
                Mage::helper('mana_db')->updateDefaultableField($object, 'state_height', Mana_Filters_Resource_Filter2::DM_STATE_HEIGHT, $fields, $useDefault);
                Mage::helper('mana_db')->updateDefaultableField($object, 'state_border_radius', Mana_Filters_Resource_Filter2::DM_STATE_BORDER_RADIUS, $fields, $useDefault);
                Mage::helper('mana_db')->updateDefaultableField($object, 'state_image', Mana_Filters_Resource_Filter2::DM_STATE_IMAGE, $fields, $useDefault);
                Mage::helper('mana_db')->updateDefaultableField($object, 'color_state_display', Mana_Filters_Resource_Filter2::DM_COLOR_STATE_DISPLAY, $fields, $useDefault);
				break;
            case 'mana_filters/filter2_value':
            case 'mana_filters/filter2_value_store':
                Mage::helper('mana_db')->updateDefaultableField($object, 'color', Mana_Filters_Resource_Filter2_Value::DM_COLOR, $fields, $useDefault);
                Mage::helper('mana_db')->updateDefaultableField($object, 'normal_image', Mana_Filters_Resource_Filter2_Value::DM_NORMAL_IMAGE, $fields, $useDefault);
                Mage::helper('mana_db')->updateDefaultableField($object, 'selected_image', Mana_Filters_Resource_Filter2_Value::DM_SELECTED_IMAGE, $fields, $useDefault);
                Mage::helper('mana_db')->updateDefaultableField($object, 'normal_hovered_image', Mana_Filters_Resource_Filter2_Value::DM_NORMAL_HOVERED_IMAGE, $fields, $useDefault);
                Mage::helper('mana_db')->updateDefaultableField($object, 'selected_hovered_image', Mana_Filters_Resource_Filter2_Value::DM_SELECTED_HOVERED_IMAGE, $fields, $useDefault);
                Mage::helper('mana_db')->updateDefaultableField($object, 'state_image', Mana_Filters_Resource_Filter2_Value::DM_STATE_IMAGE, $fields, $useDefault);
                break;
		}
	}
	/**
	 * Adds changes from color grid to the model before saving it (handles event "m_db_add_edited_details")
	 * @param Varien_Event_Observer $observer
	 */
	public function addGridData($observer) {
        /* @var $object Mana_Db_Model_Object */ $object = $observer->getEvent()->getObject();
        /* @var $request Mage_Core_Controller_Request_Http */ $request = $observer->getEvent()->getRequest();

        switch ($object->getEntityName()) {
            case 'mana_filters/filter2':
            case 'mana_filters/filter2_store':
                if ($edit = $request->getParam('colorsGrid_table')) {
                    $edit = json_decode(base64_decode($edit), true);
                    Mage::helper('mana_admin')->processPendingEdits('mana_filters/filter2_value', $edit);
                    $object->setData('value_data', Mage::helper('mana_admin')->mergeEdits($object->getData('value_data'), $edit));
                }
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
		
		$t = Mage::helper('manapro_filtercolors');
        switch ($object->getEntityName()) {
            case 'mana_filters/filter2':
            case 'mana_filters/filter2_store':
                if (trim($object->getImageWidth()) === '') {
                    $result->addError($t->__('Please fill in %s field', $t->__('Width (Picker)')));
                }
                elseif (!is_numeric($object->getImageWidth()) || $object->getImageWidth() <= 0) {
                    $result->addError($t->__('%s is not positive number', $t->__('Width (Picker)')));
                }
                if (trim($object->getImageHeight()) === '') {
                    $result->addError($t->__('Please fill in %s field', $t->__('Height (Picker)')));
                }
                elseif (!is_numeric($object->getImageHeight()) || $object->getImageHeight() <= 0) {
                    $result->addError($t->__('%s is not positive number', $t->__('Height (Picker)')));
                }
                if (trim($object->getImageBorderRadius()) === '') {
                    $result->addError($t->__('Please fill in %s field', $t->__('Border Radius (Picker)')));
                }
                elseif (!is_numeric($object->getImageBorderRadius()) || $object->getImageBorderRadius() < 0) {
                    $result->addError($t->__('%s is not non-negative number', $t->__('Border Radius (Picker)')));
                }
                if (trim($object->getStateWidth()) === '') {
                    $result->addError($t->__('Please fill in %s field', $t->__('Width (State)')));
                }
                elseif (!is_numeric($object->getStateWidth()) || $object->getStateWidth() <= 0) {
                    $result->addError($t->__('%s is not positive number', $t->__('Width (State)')));
                }
                if (trim($object->getStateHeight()) === '') {
                    $result->addError($t->__('Please fill in %s field', $t->__('Height (State)')));
                }
                elseif (!is_numeric($object->getStateHeight()) || $object->getStateHeight() <= 0) {
                    $result->addError($t->__('%s is not positive number', $t->__('Height (State)')));
                }
                if (trim($object->getStateBorderRadius()) === '') {
                    $result->addError($t->__('Please fill in %s field', $t->__('Border Radius (State)')));
                }
                elseif (!is_numeric($object->getStateBorderRadius()) || $object->getStateBorderRadius() < 0) {
                    $result->addError($t->__('%s is not non-negative number', $t->__('Border Radius (State)')));
                }
                break;
        }
	}
	/**
	 * Moves image files to permanent location (handles event "model_save_commit_after")
	 * @param Varien_Event_Observer $observer
	 */
	public function updateFiles($observer) {
        /* @var $object Mana_Db_Model_Object */ $object = $observer->getEvent()->getObject();
        /* @var $files Mana_Core_Helper_Files */ $files = Mage::helper(strtolower('Mana_Core/Files'));

        switch ($object->getEntityName()) {
            case 'mana_filters/filter2':
            case 'mana_filters/filter2_store':
                foreach (array('image_normal', 'image_selected', 'image_normal_hovered', 'image_selected_hovered', 'state_image') as $field) {
                    if ($relativeUrl = $object->getData($field)) {
                        if ($sourcePath = $files->getFilename($relativeUrl, 'temp/image')) {
                            $targetPath = $files->getFilename($relativeUrl, 'image', true);
                            if (file_exists($targetPath)) {
                                unlink($targetPath);
                            }
                            copy($sourcePath, $targetPath);
                            unlink($sourcePath);
                        }

                    }
                }
                break;
            case 'mana_filters/filter2_value':
            case 'mana_filters/filter2_value_store':
                foreach (array('normal_image', 'selected_image', 'normal_hovered_image', 'selected_hovered_image', 'state_image') as $field) {
                    if ($relativeUrl = $object->getData($field)) {
                        if ($sourcePath = $files->getFilename($relativeUrl, 'temp/image')) {
                            $targetPath = $files->getFilename($relativeUrl, 'image', true);
                            if (file_exists($targetPath)) {
                                unlink($targetPath);
                            }
                            copy($sourcePath, $targetPath);
                            unlink($sourcePath);
                        }

                    }
                }
                break;
        }
	}
	/**
	 * Generates CSS file (handles event "m_saved")
	 * @param Varien_Event_Observer $observer
	 */
	public function generateCss($observer) {
        /* @var $object Mana_Db_Model_Object */ $object = $observer->getEvent()->getObject();
        /* @var $colors ManaPro_FilterColors_Helper_Data */ $colors = Mage::helper(strtolower('ManaPro_FilterColors'));

        switch ($object->getEntityName()) {
            case 'mana_filters/filter2':
                foreach (Mage::app()->getStores() as $store) {
                    $colors->generateCss($store->getId());
                }
                break;
            case 'mana_filters/filter2_store':
                $colors->generateCss($object->getData('store_id'));
                break;
        }
	}
	/**
	 * Renders value in layered navigation state (handles event "m_filter_value_html")
	 * @param Varien_Event_Observer $observer
	 */
	public function renderValue($observer) {
        /* @var $block Mana_Filters_Block_State */ $block = $observer->getEvent()->getBlock();
	    /* @var $item Mana_Filters_Model_Item */ $item = $observer->getEvent()->getItem();
        /* @var $result Varien_Object */ $result = $observer->getEvent()->getResult();

        /* @var $layout Mage_Core_Model_Layout */
        $layout = Mage::getSingleton(strtolower('Core/Layout'));

        /* @var $filter Mana_Filters_Model_Filter_Attribute */
        $filter = $item->getFilter();
        if (!empty($filter->getFilterOptions()->getDisplayOptions()->color_state_display)) {
            $display = $filter->getFilterOptions()->getData('color_state_display');
            if (!$display) {
                $display = (string)$filter->getFilterOptions()->getDisplayOptions()->color_state_display;
            }
            if ($template = (string)Mage::getConfig()->getNode("mana_filters/color_state_display/$display/template")) {
                $result->setHtml($layout->getBlockSingleton('manapro_filtercolors/state')
                    ->setItem($item)
                    ->setBlock($block)
                    ->setTemplate($template)
                    ->toHtml()
                );
            }
        }
	}
}