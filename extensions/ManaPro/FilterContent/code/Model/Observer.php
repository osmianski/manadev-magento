<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterContent
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * This class observes certain (defined in etc/config.xml) events in the whole system and provides public methods - 
 * handlers for these events.
 * @author Mana Team
 *
 */
class ManaPro_FilterContent_Model_Observer {
    #region Initial Meta Data
    /**
     * @param Mage_Page_Block_Html_Head $head
     * @return string
     */
    protected function _getInitialTitle($head) {
        $title = $head->getData('title');
        if (($prefix = Mage::getStoreConfig('design/head/title_prefix')) && $this->coreHelper()->startsWith($title, $prefix)) {
            $title = substr($title, strlen($prefix) + 1);
        }
        if (($suffix = Mage::getStoreConfig('design/head/title_suffix')) && $this->coreHelper()->endsWith($title, $suffix)) {
            $title = substr($title, 0, strlen($title) - strlen($suffix) - 1);
        }

        return trim($title);
    }

    /**
     * @param Mage_Page_Block_Html_Head $head
     * @return string
     */
    protected function _getInitialKeywords($head) {
        $result = $head->getData('keywords');

        return $result;
    }

    /**
     * @param Mage_Page_Block_Html_Head $head
     * @return string
     */
    protected function _getInitialDescription($head) {
        $result = $head->getData('description');

        return $result;
    }
    #endregion

    /**
     * Handles event "controller_action_layout_generate_blocks_after".
     * @param Varien_Event_Observer $observer
     */
    public function addCustomContentToGeneratedBlocks($observer) {
        /* @var $layout Mage_Core_Model_Layout */
        $layout = $observer->getEvent()->getData('layout');

        $this->rendererHelper()->render();

        if ($head = $layout->getBlock('head')) {
            /* @var $head Mage_Page_Block_Html_Head */

            if (($title = $this->rendererHelper()->get('meta_title')) !== false) {
                $head->setTitle($title);
            }
            if (($keywords = $this->rendererHelper()->get('meta_keywords')) !== false) {
                $head->setData('keywords', $keywords);
            }
            if (($description = $this->rendererHelper()->get('meta_description')) !== false) {
                $head->setData('description', $description);
            }
            if (($robots = $this->rendererHelper()->get('meta_robots')) !== false) {
                $head->setData('robots', trim($robots));
            }
        }
    }

    /**
     * Handles event "core_block_abstract_to_html_before".
     * @param Varien_Event_Observer $observer
     */
    public function addCustomContentToBlockBeforeRendering($observer) {
        /* @var $block Mage_Core_Block_Abstract */
        $block = $observer->getEvent()->getBlock();
        foreach ($block->getData() as $key => $value) {
            if ($helper = $this->factoryHelper()->createBlockHelper($key, $value)) {
                $helper->before($block, $key);
            }
        }
    }

    /**
     * Handles event "core_block_abstract_to_html_after".
     * @param Varien_Event_Observer $observer
     */
    public function restoreOriginalBlockContentAfterRenderingAndPostProcess($observer) {
        /* @var $block Mage_Core_Block_Abstract */
        $block = $observer->getEvent()->getData('block');

        /* @var $transport Varien_Object */
        $htmlObject = $observer->getEvent()->getData('transport');

        foreach ($block->getData() as $key => $value) {
            if ($helper = $this->factoryHelper()->createBlockHelper($key, $value)) {
                $helper->after($block, $key, $htmlObject);
            }
        }
    }

    protected $_fields = array(
        'content_is_active' => array(
            'bit' => Mana_Filters_Resource_Filter2_Value::DM_CONTENT_IS_ACTIVE,
            'label' => 'Additional Content',
        ),
        'content_is_initialized' => array(
            'bit' => Mana_Filters_Resource_Filter2_Value::DM_CONTENT_IS_INITIALIZED,
            'label' => 'Is Initialized',
        ),
        'content_priority' => array(
            'bit' => Mana_Filters_Resource_Filter2_Value::DM_CONTENT_PRIORITY,
            'label' => 'Priority',
        ),
        'content_stop_further_processing' => array(
            'bit' => Mana_Filters_Resource_Filter2_Value::DM_CONTENT_STOP_FURTHER_PROCESSING,
            'label' => 'Stop Further Processing',
        ),
        'content_meta_title' => array(
            'bit' => Mana_Filters_Resource_Filter2_Value::DM_CONTENT_META_TITLE,
            'validation' => 'twig',
            'label' => 'Page Title',
        ),
        'content_meta_keywords' => array(
            'bit' => Mana_Filters_Resource_Filter2_Value::DM_CONTENT_META_KEYWORDS,
            'validation' => 'twig',
            'label' => 'Meta Keywords',
        ),
        'content_meta_description' => array(
            'bit' => Mana_Filters_Resource_Filter2_Value::DM_CONTENT_META_DESCRIPTION,
            'validation' => 'twig',
            'label' => 'Meta Description',
        ),
        'content_meta_robots' => array(
            'bit' => Mana_Filters_Resource_Filter2_Value::DM_CONTENT_META_ROBOTS,
            'validation' => 'twig',
            'label' => 'Meta Robots',
        ),
        'content_title' => array(
            'bit' => Mana_Filters_Resource_Filter2_Value::DM_CONTENT_TITLE,
            'validation' => 'twig',
            'label' => 'Title (H1)',
        ),
        'content_subtitle' => array(
            'bit' => Mana_Filters_Resource_Filter2_Value::DM_CONTENT_SUBTITLE,
            'validation' => 'twig',
            'label' => 'Subtitle',
        ),
        'content_description' => array(
            'bit' => Mana_Filters_Resource_Filter2_Value::DM_CONTENT_DESCRIPTION,
            'validation' => 'twig',
            'label' => 'Description',
        ),
        'content_additional_description' => array(
            'bit' => Mana_Filters_Resource_Filter2_Value::DM_CONTENT_ADDITIONAL_DESCRIPTION,
            'validation' => 'twig',
            'label' => 'Additional Description',
        ),
        'content_layout_xml' => array(
            'bit' => Mana_Filters_Resource_Filter2_Value::DM_CONTENT_LAYOUT_XML,
            'validation' => 'twig',
            'label' => 'Layout XML',
        ),
        'content_widget_layout_xml' => array(
            'bit' => Mana_Filters_Resource_Filter2_Value::DM_CONTENT_WIDGET_LAYOUT_XML,
            'validation' => 'twig',
            'label' => 'Widget Layout XML',
        ),
        'content_common_directives' => array(
            'bit' => Mana_Filters_Resource_Filter2_Value::DM_CONTENT_COMMON_DIRECTIVES,
            'validation' => 'twig',
            'label' => 'Widget Layout XML',
        ),
        'content_background_image' => array(
            'bit' => Mana_Filters_Resource_Filter2_Value::DM_CONTENT_BACKGROUND_IMAGE,
            'label' => 'Background Image for Additional Description',
            'image' => true,
        ),
    );
	/**
	 * Adds columns to replication update select (handles event "m_db_update_columns")
	 * @param Varien_Event_Observer $observer
	 */
	public function prepareUpdateColumns($observer) {
		/* @var $target Mana_Db_Model_Replication_Target */
		$target = $observer->getEvent()->getData('target');

		switch ($target->getEntityName()) {
			case 'mana_filters/filter2_value_store':
			    $columns = array();
			    foreach (array_keys($this->_fields) as $field) {
                    $columns[] = "global.$field AS $field";
			    }
				$target->getSelect('main')->columns($columns);
				break;
		}
	}

	/**
	 * Adds values to be updated (handles event "m_db_update_process")
	 * @param Varien_Event_Observer $observer
	 */
	public function processUpdate($observer) {
		/* @var $object Mana_Db_Model_Object */
		$object = $observer->getEvent()->getData('object');
		/* @var $values array */
		$values = $observer->getEvent()->getData('values');

		switch ($object->getEntityName()) {
            case 'mana_filters/filter2_value_store':
                foreach ($this->_fields as $field => $fieldDef) {
                    if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, $fieldDef['bit'])) {
                        $object->setData($field, $values[$field]);
                    }
			    }
				break;
		}
	}

	/**
	 * Adds columns to replication insert select (handles event "m_db_insert_columns")
	 * @param Varien_Event_Observer $observer
	 */
	public function prepareInsertColumns($observer) {
		/* @var $target Mana_Db_Model_Replication_Target */
		$target = $observer->getEvent()->getData('target');

		switch ($target->getEntityName()) {
			case 'mana_filters/filter2_value_store':
			    $columns = array();
			    foreach (array_keys($this->_fields) as $field) {
                    $columns[] = "global.$field AS $field";
			    }
				$target->getSelect('main')->columns($columns);
				break;
		}
	}

	/**
	 * Adds values to be inserted (handles event "m_db_insert_process")
	 * @param Varien_Event_Observer $observer
	 */
	public function processInsert($observer) {
        /* @var $object Mana_Db_Model_Object */
        $object = $observer->getEvent()->getData('object');
        /* @var $values array */
        $values = $observer->getEvent()->getData('values');

        switch ($object->getEntityName()) {
            case 'mana_filters/filter2_value_store':
                foreach ($this->_fields as $field => $fieldDef) {
                    $object->setData($field, $values[$field]);
			    }
                break;
		}
	}

	/**
	 * Adds changes from color grid to the model before saving it (handles event "m_db_add_edited_details")
	 * @param Varien_Event_Observer $observer
	 */
	public function addGridData($observer) {
        /* @var $object Mana_Db_Model_Object */
        $object = $observer->getEvent()->getData('object');
        /* @var $request Mage_Core_Controller_Request_Http */
        $request = $observer->getEvent()->getData('request');

        switch ($object->getEntityName()) {
            case 'mana_filters/filter2':
            case 'mana_filters/filter2_store':
                if ($data = $request->getParam('content-grid')) {
                    $data = json_decode($data, true);
                    $edit = json_decode($data['edit'], true);
                    Mage::helper('mana_admin')->processPendingEdits('mana_filters/filter2_value', $edit);
                    $object->setData('value_data', Mage::helper('mana_admin')->mergeEdits($object->getData('value_data'), $edit));
                }
                break;
        }
	}

	/**
	 * Validates edited data (handles event "m_db_validate")
	 * @param Varien_Event_Observer $observer
	 */
	public function validate($observer) {
        /* @var $object Mana_Db_Model_Object */
        $object = $observer->getEvent()->getData('object');
        /* @var $result Mana_Db_Model_Validation */
        $result = $observer->getEvent()->getData('result');

        switch ($object->getEntityName()) {
            case 'mana_filters/filter2_value':
            case 'mana_filters/filter2_value_store':
                foreach ($this->_fields as $field => $fieldDef) {
                    if (!empty($fieldDef['image'])) {
                        if ($this->coreDbHelper()->isModelContainsCustomSetting($object, $field) &&
                            ($relativeUrl = $object->getData($field)))
                        {
                            if ($sourcePath = $this->fileHelper()->getFilename($relativeUrl, 'temp/image')) {
                                $targetPath = $this->fileHelper()->getFilename($relativeUrl, 'image', true);
                                if (file_exists($targetPath)) {
                                    unlink($targetPath);
                                }
                                copy($sourcePath, $targetPath);
                                unlink($sourcePath);
                            }

                        }
                    }
                    if (isset($fieldDef['validation'])) {
                        switch ($fieldDef['validation']) {
                            case 'twig':
                                try {
                                    $this->twigHelper()->renderContentRule($object->getData($field), array());
                                }
                                catch (Exception $e){
                                    $result->addError($this->helper()->__("<p>%s '%s' error: </p><p>%s</p>",
                                        $this->getOptionResource()->getDefaultOptionLabel($object->getData('option_id')),
                                        $this->helper()->__($fieldDef['label']),
                                        nl2br(htmlspecialchars($e->getMessage())))
                                    );
                                }
                                break;
                        }
                    }
                }
                break;
        }
	}

    /**
     * Adds edited data received via HTTP to specified model (handles event "m_db_add_edited_data")
     * @param Varien_Event_Observer $observer
     */
    public function addEditedData($observer) {
        /* @var $object Mana_Db_Model_Object */
        $object = $observer->getEvent()->getData('object');
        /* @var $fields array */
        $fields = $observer->getEvent()->getData('fields');
        /* @var $useDefault array */
        $useDefault = $observer->getEvent()->getData('use_default');

        switch ($object->getEntityName()) {
            case 'mana_filters/filter2_value':
            case 'mana_filters/filter2_value_store':
                foreach ($this->_fields as $field => $fieldDef) {
                    Mage::helper('mana_db')->updateDefaultableField($object, $field, $fieldDef['bit'], $fields, $useDefault);
                }
                break;
        }
    }

    #region Dependencies
    /**
     * @return ManaPro_FilterContent_Helper_Data
     */
    public function helper() {
        return Mage::helper('manapro_filtercontent');
    }

    /**
     * @return ManaPro_FilterContent_Helper_Factory
     */
    public function factoryHelper() {
        return Mage::helper('manapro_filtercontent/factory');
    }

    /**
     * @return Mana_Core_Helper_Data
     */
    public function coreHelper() {
        return Mage::helper('mana_core');
    }
    /**
     * @return ManaPro_FilterContent_Helper_Renderer
     */
    public function rendererHelper() {
        return Mage::helper('manapro_filtercontent/renderer');
    }

    /**
     * @return Mana_Twig_Helper_Data
     */
    public function twigHelper() {
        return Mage::helper('mana_twig');
    }

    /**
     * @return ManaPro_FilterContent_Resource_Option
     */
    public function getOptionResource() {
        return Mage::getResourceSingleton('manapro_filtercontent/option');
    }

    /**
     * @return Mana_Core_Helper_Files
     */
    public function fileHelper() {
        return Mage::helper('mana_core/files');
    }

    /**
     * @return Mana_Core_Helper_Db
     */
    public function coreDbHelper() {
        return Mage::helper('mana_core/db');
    }
    #endregion
}