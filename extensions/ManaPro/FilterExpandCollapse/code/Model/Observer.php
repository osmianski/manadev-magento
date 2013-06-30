<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterExpandCollapse
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * This class observes certain (defined in etc/config.xml) events in the whole system and provides public methods -
 * handlers for these events.
 * @author Mana Team
 *
 */
class ManaPro_FilterExpandCollapse_Model_Observer {
	/**
	 * Raises flag is config value changed this module's replicated tables rely on (handles event "m_db_is_config_changed")
	 * @param Varien_Event_Observer $observer
	 */
	public function isConfigChanged($observer) {
		/* @var $result Varien_Object */ $result = $observer->getEvent()->getResult();
		/* @var $configData Mage_Core_Model_Config_Data */ $configData = $observer->getEvent()->getConfigData();

		Mage::helper('mana_db')->checkIfPathsChanged($result, $configData, array(
			'mana_filters/advanced/collapseable',
        ));
	}
    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "m_db_update_columns")
     * @param Varien_Event_Observer $observer
     */
    public function prepareUpdateColumns($observer) {
		/* @var $target Mana_Db_Model_Replication_Target */ $target = $observer->getEvent()->getTarget();
		/* @var $options array */ $options = $observer->getEvent()->getOptions();

		switch ($target->getEntityName()) {
			case 'mana_filters/filter2_store':
				$target->getSelect('main')->columns(array(
					'global.collapseable AS collapseable',
                ));
				break;
		}
    }
    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "m_db_update_process")
     * @param Varien_Event_Observer $observer
     */
    public function processUpdate($observer) {
		/* @var $object Mana_Db_Model_Object */ $object = $observer->getEvent()->getObject();
		/* @var $values array */ $values = $observer->getEvent()->getValues();
		/* @var $options array */ $options = $observer->getEvent()->getOptions();

		switch ($object->getEntityName()) {
			case 'mana_filters/filter2':
				if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_COLLAPSEABLE)) {
					$object->setCollapseable(Mage::helper('mana_db')->getLatestConfig('mana_filters/advanced/collapseable'));
				}
                break;
			case 'mana_filters/filter2_store':
				if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_COLLAPSEABLE)) {
					$object->setCollapseable($values['collapseable']);
				}
                break;
		}
    }
    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "m_db_insert_columns")
     * @param Varien_Event_Observer $observer
     */
    public function prepareInsertColumns($observer) {
		/* @var $target Mana_Db_Model_Replication_Target */ $target = $observer->getEvent()->getTarget();
		/* @var $options array */ $options = $observer->getEvent()->getOptions();

		switch ($target->getEntityName()) {
			case 'mana_filters/filter2_store':
				$target->getSelect('main')->columns(array(
					'global.collapseable AS collapseable',
                ));
				break;
		}
    }
    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "m_db_insert_process")
     * @param Varien_Event_Observer $observer
     */
    public function processInsert($observer) {
		/* @var $object Mana_Db_Model_Object */ $object = $observer->getEvent()->getObject();
		/* @var $values array */ $values = $observer->getEvent()->getValues();
		/* @var $options array */ $options = $observer->getEvent()->getOptions();

		switch ($object->getEntityName()) {
			case 'mana_filters/filter2':
        		$object->setCollapseable(Mage::helper('mana_db')->getLatestConfig('mana_filters/advanced/collapseable'));
                break;
			case 'mana_filters/filter2_store':
				$object->setCollapseable($values['collapseable']);
                break;
		}
    }
    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "m_db_add_edited_data")
     * @param Varien_Event_Observer $observer
     */
    public function addEditedData($observer) {
		/* @var $object Mana_Db_Model_Object */ $object = $observer->getEvent()->getObject();
		/* @var $fields array */ $fields = $observer->getEvent()->getFields();
		/* @var $useDefault array */ $useDefault = $observer->getEvent()->getUseDefault();

		switch ($object->getEntityName()) {
			case 'mana_filters/filter2':
			case 'mana_filters/filter2_store':
				Mage::helper('mana_db')->updateDefaultableField($object, 'collapseable', Mana_Filters_Resource_Filter2::DM_COLLAPSEABLE, $fields, $useDefault);
                break;
		}
    }

    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "m_crud_form")
     * @param Varien_Event_Observer $observer
     */
    public function addFields($observer) {
        /* @var $formBlock Mana_Admin_Block_Crud_Card_Form */ /** @noinspection PhpUndefinedMethodInspection */
        $formBlock = $observer->getEvent()->getForm();
        $form = $formBlock->getForm();
        /* @var $layout Mage_Core_Model_Layout */
        $layout = Mage::getSingleton(strtolower('Core/Layout'));
        /* @var $fieldsetRenderer Mana_Admin_Block_Crud_Card_Fieldset */ $fieldsetRenderer =
        $layout->getBlockSingleton(strtolower('Mana_Admin/Crud_Card_Fieldset'));
        /* @var $fieldRenderer Mana_Admin_Block_Crud_Card_Field */ $fieldRenderer =
        $layout->getBlockSingleton(strtolower('Mana_Admin/Crud_Card_Field'));
        /* @var $admin Mana_Admin_Helper_Data */ $admin = Mage::helper(strtolower('Mana_Admin'));
        /* @var $t ManaPro_FilterHelp_Helper_Data */ $t = Mage::helper(strtolower('ManaPro_FilterExpandCollapse'));
        /* @var $model Mana_Filters_Model_Filter2 */
        /** @noinspection PhpUndefinedMethodInspection */
        $model = $form->getModel();

        switch ($formBlock->getEntityName()) {
            case 'mana_filters/filter2':
            case 'mana_filters/filter2_store':
                if ($form->getId() == 'mf_advanced') {
                    // fieldset - collection of fields
                    $fieldset = $form->addFieldset('mfs_collapseable', array(
                        'title' => $t->__('Expand/Collapse'),
                        'legend' => $t->__('Expand/Collapse'),
                    ));
                    $fieldset->setRenderer($fieldsetRenderer);

//                    $widgetFilters = array('is_email_compatible' => 1);
//                    $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig(array('widget_filters' => $widgetFilters));
                    $field = $fieldset->addField('collapseable', 'select', array(
                        'label' => $t->__('Expand/Collapse/Dropdown Method'),
                        'name' => 'collapseable',
                        'required' => false,
                        'options' => Mage::getSingleton('manapro_filterexpandcollapse/source_method')->getOptionArray(),
                        'default_bit' => Mana_Filters_Resource_Filter2::DM_COLLAPSEABLE,
						'default_label' => Mage::helper('mana_admin')->isGlobal()
							? $t->__('Use System Configuration')
							: $t->__('Same For All Stores'),
                    ));
                    $field->setRenderer($fieldRenderer);

                }
                break;
        }
    }

    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "m_advanced_filter_name_css")
     * @param Varien_Event_Observer $observer
     */
    public function renderCss($observer) {
        /* @var $block Mana_Filters_Block_View */ $block = $observer->getEvent()->getBlock();
        /* @var $filter Mana_Filters_Block_Filter */ $filter = $observer->getEvent()->getFilter();
        /* @var $helper ManaPro_FilterExpandCollapse_Helper_Data */ $helper = Mage::helper(strtolower('ManaPro_FilterExpandCollapse'));

        if ($options = $filter->getFilterOptions()) {
            /* @var $options Mana_Filters_Model_Filter2_Store */
            switch ($helper->isCollapseable($block, $options)) {
                case 'expand':
                case 'collapse':
                    echo ' m-collapseable';
                    break;
                case 'dropdown':
                    echo ' m-dropdown-menu';
                    break;
            }
        }
    }

    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "m_advanced_filter_value_css")
     * @param Varien_Event_Observer $observer
     */
    public function renderValueCss($observer) {
        /* @var $block Mana_Filters_Block_View */
        $block = $observer->getEvent()->getBlock();
        /* @var $filter Mana_Filters_Block_Filter */
        $filter = $observer->getEvent()->getFilter();
        /* @var $helper ManaPro_FilterExpandCollapse_Helper_Data */
        $helper = Mage::helper(strtolower('ManaPro_FilterExpandCollapse'));

        if ($options = $filter->getFilterOptions()) {
            /* @var $options Mana_Filters_Model_Filter2_Store */
            switch ($helper->isCollapseable($block, $options)) {
                case 'dropdown':
                    echo ' m-dropdown-menu';
                    break;
            }
        }
    }

    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "m_advanced_filter_name_attributes")
     * @param Varien_Event_Observer $observer
     */
    public function renderAttributes($observer) {
        /* @var $block Mana_Filters_Block_View */ $block = $observer->getEvent()->getBlock();
        /* @var $filter Mana_Filters_Block_Filter */ $filter = $observer->getEvent()->getFilter();
        /* @var $helper ManaPro_FilterExpandCollapse_Helper_Data */ $helper = Mage::helper(strtolower('ManaPro_FilterExpandCollapse'));

        if ($options = $filter->getFilterOptions()) {
            /* @var $options Mana_Filters_Model_Filter2_Store */
            switch ($helper->isCollapseable($block, $options)) {
                case 'collapse':
                    if (Mage::app()->getFrontController()->getRequest()->getParam($filter->getFilter()->getRequestVar()) == $filter->getFilter()->getResetValue()) {
                        echo ' data-collapsed="collapsed"';
                    }
                    break;
            }
        }
    }
    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "m_advanced_filter_name_action")
     * @param Varien_Event_Observer $observer
     */
    public function renderAction($observer) {
        /* @var $block Mana_Filters_Block_View */ $block = $observer->getEvent()->getBlock();
        /* @var $filter Mana_Filters_Block_Filter */ $filter = $observer->getEvent()->getFilter();
    	/* @var $layout Mage_Core_Model_Layout */ $layout = Mage::getSingleton('core/layout');
    	/* @var $result Varien_Object */ $result = $observer->getEvent()->getResult();
    	if ($helperBlock = $layout->getBlock('m_filter_expandcollapse')) {
            if ($html = trim($helperBlock->setLayer($block)->setFilter($filter)->toHtml())) {
                $actions = $result->getResult();
                $actions[] = array('html' => $html, 'position' => 200);
                $result->setResult($actions);
            }
        }
    }
}