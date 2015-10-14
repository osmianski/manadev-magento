<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterShowMore
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
class ManaPro_FilterShowMore_Model_Observer {
	/* BASED ON SNIPPET: Models/Event handler */
	/**
	 * In case filter item list is too long, truncates it and sets a flag on whole filter to enable additional 
	 * show more/show less actions on it. 
	 * @param Varien_Event_Observer $observer
	 */
	public function limitNumberOfVisibleItems($observer) {
		/* @var $filter Mage_Catalog_Model_Layer_Filter_Abstract */ $filter = $observer->getEvent()->getFilter();
		/* @var $items Varien_Object */ $items = $observer->getEvent()->getItems();
		
		/* @var $_helper ManaPro_FilterShowMore_Helper_Data */ $_helper = Mage::helper(strtolower('ManaPro_FilterShowMore'));
		if ($filter->getFilterOptions()->getShowMoreMethod() != 'popup') {
			$filter->setMIsShowMoreApplied(count($items->getItems()) > $filter->getFilterOptions()->getShowMoreItemCount());
		}
		elseif (!Mage::registry('m_showing_filter_popup')) {
			if (!$filter->getMIsShowMoreDisabled()) {
				$maxItemCount = $filter->getFilterOptions()->getShowMoreItemCount();
				if (count($items->getItems()) > $maxItemCount) {
				    $newItems = array();
				    $index = 0;
				    foreach ($items->getItems() as $key => $item) {
				        /* @var $item Mana_Filters_Model_Item */
				        if (Mage::getStoreConfigFlag('mana_filters/display/add_space_for_not_visible_selected_items')) {
							if ($index < $maxItemCount || $item->getMSelected()) {
								$newItems[$key] = $item;
							}
				        }
				        else {
							if ($index < $maxItemCount) {
								$newItems[$key] = $item;
							}
				        }
				        $index++;
				    }
					if (!$_helper->isShowAllRequested($filter)) {
						$items->setItems($newItems);
					}
					$filter->setMIsShowMoreApplied(true);
				}
			}
		}
	}
	
	/* BASED ON SNIPPET: Models/Event handler */
	/**
	 * Raises flag is config value changed this module's replicated tables rely on (handles event "m_db_is_config_changed")
	 * @param Varien_Event_Observer $observer
	 */
	public function isConfigChanged($observer) {
		/* @var $result Varien_Object */ $result = $observer->getEvent()->getResult();
		/* @var $configData Mage_Core_Model_Config_Data */ $configData = $observer->getEvent()->getConfigData();
		
		Mage::helper('mana_db')->checkIfPathsChanged($result, $configData, array(
			'mana_filters/display/show_more_item_count',
            'mana_filters/display/show_more_method',
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
					'global.show_more_item_count AS show_more_item_count',
                    'global.show_more_method AS show_more_method',
                    'global.show_option_search AS show_option_search',
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
				if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_SHOW_MORE_ITEM_COUNT)) {
					$object->setShowMoreItemCount(Mage::helper('mana_db')->getLatestConfig('mana_filters/display/show_more_item_count'));
				}
                if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_SHOW_MORE_METHOD)) {
                    $object->setShowMoreMethod(Mage::helper('mana_db')->getLatestConfig('mana_filters/display/show_more_method'));
                }
                break;
			case 'mana_filters/filter2_store':
				if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_SHOW_MORE_ITEM_COUNT)) {
					$object->setShowMoreItemCount($values['show_more_item_count']);
				}
                if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_SHOW_MORE_METHOD)) {
                    $object->setShowMoreMethod($values['show_more_method']);
                }
                if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_SHOW_OPTION_SEARCH)) {
                    $object->setData('show_option_search', $values['show_option_search']);
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
					'global.show_more_item_count AS show_more_item_count',
                    'global.show_more_method AS show_more_method',
                    'global.show_option_search AS show_option_search',
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
				$object->setShowMoreItemCount(Mage::helper('mana_db')->getLatestConfig('mana_filters/display/show_more_item_count'));
                $object->setShowMoreMethod(Mage::helper('mana_db')->getLatestConfig('mana_filters/display/show_more_method'));
                break;
			case 'mana_filters/filter2_store':
				$object->setShowMoreItemCount($values['show_more_item_count']);
                $object->setShowMoreMethod($values['show_more_method']);
                $object->setData('show_option_search', $values['show_option_search']);
                break;
		}
	}
	/* BASED ON SNIPPET: Models/Event handler */
	/**
	 * Adds fields into CRUD form (handles event "m_crud_form")
	 * @param Varien_Event_Observer $observer
	 */
	public function addFields($observer) {
		/* @var $formBlock Mana_Admin_Block_Crud_Card_Form */ $formBlock = $observer->getEvent()->getForm();
		$form = $formBlock->getForm();
		
		switch ($formBlock->getEntityName()) {
			case 'mana_filters/filter2':
			case 'mana_filters/filter2_store':
				if ($form->getId() == 'mf_general') {
					$field = $form->getElement('mfs_display')->addField('show_more_item_count', 'text', array(
						'label' => Mage::helper('manapro_filtershowmore')->__('Item Limit'),
						'name' => 'show_more_item_count',
						'required' => true,
						'default_bit' => Mana_Filters_Resource_Filter2::DM_SHOW_MORE_ITEM_COUNT,
						'default_label' => Mage::helper('mana_admin')->isGlobal() 
							? Mage::helper('manapro_filtershowmore')->__('Use System Configuration') 
							: Mage::helper('manapro_filtershowmore')->__('Same For All Stores'),
					), 'position');
					$field->setRenderer(Mage::getSingleton('core/layout')->getBlockSingleton('mana_admin/crud_card_field'));

                    $field = $form->getElement('mfs_display')->addField('show_more_method', 'select', array(
                        'label' => Mage::helper('manapro_filtershowmore')->__('Method of Showing All Items'),
                        'name' => 'show_more_method',
                        'required' => true,
                        'options' => Mage::getSingleton('manapro_filtershowmore/source_method')->getOptionArray(),
                        'default_bit' => Mana_Filters_Resource_Filter2::DM_SHOW_MORE_METHOD,
                        'default_label' => Mage::helper('mana_admin')->isGlobal()
                                ? Mage::helper('manapro_filtershowmore')->__('Use System Configuration')
                                : Mage::helper('manapro_filtershowmore')->__('Same For All Stores'),
                    ), 'show_more_item_count');
                    $field->setRenderer(Mage::getSingleton('core/layout')->getBlockSingleton('mana_admin/crud_card_field'));

                    $field = $form->getElement('mfs_display')->addField('show_option_search', 'select', array_merge(array(
                        'label' => Mage::helper('manapro_filtershowmore')->__('Show Option Search'),
                        'name' => 'show_option_search',
                        'required' => true,
                        'options' => Mage::getSingleton('mana_core/source_yesno')->getOptionArray(),
                    ), Mage::helper('mana_admin')->isGlobal() ? array() : array(
                        'default_bit' => Mana_Filters_Resource_Filter2::DM_SHOW_OPTION_SEARCH,
                        'default_label' => Mage::helper('manapro_filtershowmore')->__('Same For All Stores'),
                    )), 'show_more_method');
                    $field->setRenderer(Mage::getSingleton('core/layout')->getBlockSingleton('mana_admin/crud_card_field'));
                }
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
				Mage::helper('mana_db')->updateDefaultableField($object, 'show_more_item_count', Mana_Filters_Resource_Filter2::DM_SHOW_MORE_ITEM_COUNT, $fields, $useDefault);
                Mage::helper('mana_db')->updateDefaultableField($object, 'show_more_method', Mana_Filters_Resource_Filter2::DM_SHOW_MORE_METHOD, $fields, $useDefault);
                Mage::helper('mana_db')->updateDefaultableField($object, 'show_option_search', Mana_Filters_Resource_Filter2::DM_SHOW_OPTION_SEARCH, $fields, $useDefault);
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
                $t = Mage::helper('manapro_filtershowmore');
                if (trim($object->getShowMoreItemCount()) === '') {
                    $result->addError($t->__('Please fill in %s field', $t->__('Item Limit')));
                }
                break;
        }
	}

    /* BASED ON SNIPPET: Models/Event handler */
    /**
     * Calls minimized version of category action (handles event "controller_action_predispatch")
     * @param Varien_Event_Observer $observer
     */
    public function ajaxPopup($observer) {
        /* @var $action Mage_Catalog_CategoryController */
        $action = $observer->getEvent()->getControllerAction();
        $originalRoutePath = Mage::helper('mana_core')->getRoutePath();
        if ($action->getRequest()->getParam('m-show-more-popup') && $originalRoutePath != 'manapro_filtershowmore/popup/view') {
            Mage::register('m_original_route_path', $originalRoutePath);
            $this->_forward($action->getRequest(), 'view', 'popup', 'manapro_filtershowmore');
        }
    }
    protected function _forward($request, $action, $controller = null, $module = null, array $params = null) {
        $request->initForward();

        if (!is_null($params)) {
            $request->setParams($params);
        }

        if (!is_null($controller)) {
            $request->setControllerName($controller);

            // Module should only be reset if controller has been specified
            if (!is_null($module)) {
                $request->setModuleName($module);
            }
        }

        $request->setActionName($action)
                ->setDispatched(false);
    }
}