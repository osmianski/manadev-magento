<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterPositioning
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * This class observes certain (defined in etc/config.xml) events in the whole system and provides public methods -
 * handlers for these events.
 * @author Mana Team
 *
 */
class ManaPro_FilterPositioning_Model_Observer {
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
            'mana_filters/positioning/show_in',
        ));
    }
    /* BASED ON SNIPPET: Models/Event handler */
    /**
     * Adds columns to replication update select (handles event "m_db_update_columns")
     * @param Varien_Event_Observer $observer
     */
    public function prepareUpdateColumns($observer) {
        /* @var $target Mana_Db_Model_Replication_Target */
        $target = $observer->getEvent()->getTarget();
        /* @var $options array */
        $options = $observer->getEvent()->getOptions();

        switch ($target->getEntityName()) {
            case 'mana_filters/filter2_store':
                $target->getSelect('main')->columns(array(
                    'global.show_in AS show_in',
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
        /* @var $object Mana_Db_Model_Object */
        $object = $observer->getEvent()->getObject();
        /* @var $values array */
        $values = $observer->getEvent()->getValues();
        /* @var $options array */
        $options = $observer->getEvent()->getOptions();

        switch ($object->getEntityName()) {
            case 'mana_filters/filter2':
                if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_SHOW_IN)) {
                    $object->setShowIn(Mage::helper('mana_db')->getLatestConfig('mana_filters/positioning/show_in'));
                }
                break;
            case 'mana_filters/filter2_store':
                if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_SHOW_IN)) {
                    $object->setShowIn($values['show_in']);
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
        /* @var $target Mana_Db_Model_Replication_Target */
        $target = $observer->getEvent()->getTarget();
        /* @var $options array */
        $options = $observer->getEvent()->getOptions();

        switch ($target->getEntityName()) {
            case 'mana_filters/filter2_store':
                $target->getSelect('main')->columns(array(
                    'global.show_in AS show_in',
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
        /* @var $object Mana_Db_Model_Object */
        $object = $observer->getEvent()->getObject();
        /* @var $values array */
        $values = $observer->getEvent()->getValues();
        /* @var $options array */
        $options = $observer->getEvent()->getOptions();

        switch ($object->getEntityName()) {
            case 'mana_filters/filter2':
                $object->setShowIn(Mage::helper('mana_db')->getLatestConfig('mana_filters/positioning/show_in'));
                break;
            case 'mana_filters/filter2_store':
                $object->setShowIn($values['show_in']);
                break;
        }
    }
    /* BASED ON SNIPPET: Models/Event handler */
    /**
     * Adds fields into CRUD form (handles event "m_crud_form")
     * @param Varien_Event_Observer $observer
     */
    public function addFields($observer) {
        /* @var $formBlock Mana_Admin_Block_Crud_Card_Form */
        $formBlock = $observer->getEvent()->getForm();
        $form = $formBlock->getForm();

        switch ($formBlock->getEntityName()) {
            case 'mana_filters/filter2':
            case 'mana_filters/filter2_store':
                if ($form->getId() == 'mf_general') {
                    $field = $form->getElement('mfs_display')->addField('show_in', 'multiselect', array(
                        'label' => Mage::helper('manapro_filterpositioning')->__('Show'),
                        'name' => 'show_in',
                        'required' => true,
                        'values' => Mage::getSingleton('manapro_filterpositioning/source_show_in')->getAllOptions(),
                        'default_bit' => Mana_Filters_Resource_Filter2::DM_SHOW_IN,
                        'default_label' => Mage::helper('mana_admin')->isGlobal()
                                ? Mage::helper('manapro_filterpositioning')->__('Use System Configuration')
                                : Mage::helper('manapro_filterpositioning')->__('Same For All Stores'),
                    ), 'position');
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
        /* @var $object Mana_Db_Model_Object */
        $object = $observer->getEvent()->getObject();
        /* @var $fields array */
        $fields = $observer->getEvent()->getFields();
        /* @var $useDefault array */
        $useDefault = $observer->getEvent()->getUseDefault();

        switch ($object->getEntityName()) {
            case 'mana_filters/filter2':
            case 'mana_filters/filter2_store':
                Mage::helper('mana_db')->updateDefaultableField($object, 'show_in', Mana_Filters_Resource_Filter2::DM_SHOW_IN, $fields, $useDefault);
                if (is_array($object->getShowIn())) {
                    $object->setShowIn(implode(',', $object->getShowIn()));
                }
                break;
        }
    }

    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "m_crud_grid_collection")
     * @param Varien_Event_Observer $observer
     */
    public function prepareGridCollection($observer) {
        /* @var $grid Mana_Admin_Block_Crud_Grid */ $grid = $observer->getEvent()->getGrid();

        if ($grid instanceof ManaPro_FilterAdmin_Block_List_Grid) {
            $grid->getCollection()->addColumnToSelect(array('show_in'));
        }
    }

    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "m_crud_grid_columns")
     * @param Varien_Event_Observer $observer
     */
    public function prepareGridColumns($observer) {
        /* @var $grid Mana_Admin_Block_Crud_Grid */ $grid = $observer->getEvent()->getGrid();

        if ($grid instanceof ManaPro_FilterAdmin_Block_List_Grid) {
            $grid->addColumn('show_in', array(
                'header' => Mage::helper('manapro_filterpositioning')->__('Show'),
                'index' => 'show_in',
                'filter_index' => 'main_table.show_in',
                'width' => '150px',
                'align' => 'center',
                'type' => 'options',
                'renderer' => 'mana_admin/column_multivalue',
                'options' => Mage::getSingleton('manapro_filterpositioning/source_show_in')->getOptionArray(),
            ));
        }
    }

    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "core_block_abstract_to_html_after")
     * @param Varien_Event_Observer $observer
     */
    public function renderLayeredNavigationAboveProductList($observer) {
        /* @var $block Mage_Core_Block_Abstract */ $block = $observer->getEvent()->getBlock();
        /* @var $transport Varien_Object */ $transport = $observer->getEvent()->getData('transport');

    	if (in_array($block->getNameInLayout(), array('product_list', 'search_result_list')) &&
    	    ($parent = $block->getParentBlock()) &&
    	    ($aboveBlock = $parent->getChild('above_products')) &&
    	    !$aboveBlock->getData('m_already_rendered'))
    	{
            $productListHtml = $transport->getHtml();
            $html = $aboveBlock->toHtml();
            $transport->setData('html', $html. $productListHtml);
            $aboveBlock->setData('m_already_rendered', true);
    	}
    }

    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "core_block_abstract_to_html_before")
     * @param Varien_Event_Observer $observer
     */
    public function renderCategoryCmsBlock($observer) {
        /* @var $block Mage_Catalog_Block_Category_View */
        $block = $observer->getEvent()->getBlock();

        if ($block instanceof Mage_Catalog_Block_Category_View &&
            ($block->isContentMode() || $block->isMixedMode()) &&
            ($aboveBlock = $block->getChild('above_products')) &&
            !$aboveBlock->getData('m_already_rendered'))
        {
            /* @var $cmsBlock Mage_Cms_Block_Block */
            $cmsBlock = $block->getLayout()->createBlock('cms/block');
            $cmsBlock->setData('block_id', $block->getCurrentCategory()->getDataUsingMethod('landing_page'));
            $html = $aboveBlock->toHtml(). $cmsBlock->toHtml();
            $aboveBlock->setData('m_already_rendered', true);
            $block->setData('cms_block_html', $html);
        }
    }
    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "m_advanced_filter_currently_shopping_by")
     * @param Varien_Event_Observer $observer
     */
    public function showCurrentlyShoppingBy($observer) {
        /* @var $block Mana_Filters_Block_View */ $block = $observer->getEvent()->getBlock();
        /* @var $result Varien_Object */ $result = $observer->getEvent()->getResult();

        if ($block->getShowInFilter() == 'above_products' && !Mage::getStoreConfigFlag('mana_filters/positioning/currently_shopping_by_title')) {
            $result->setResult(false);
        }
    }
}