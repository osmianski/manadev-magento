<?php
/** 
 * @category    Mana
 * @package     Mana_AttributePage
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_AttributePage_Model_OptionPage_Indexer extends Mana_Core_Model_Indexer {
    protected $_code = 'mana_option_page';
    protected $_matchedEntities = array(
        Mage_Core_Model_Store::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE,
        ),
        'mana_seo/schema/global' => array(
            Mage_Index_Model_Event::TYPE_SAVE,
        ),
        'mana_seo/schema/store' => array(
            Mage_Index_Model_Event::TYPE_SAVE,
        ),
        Mage_Catalog_Model_Resource_Eav_Attribute::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE
        ),
        'mana_filters/filter2' => array(
            Mage_Index_Model_Event::TYPE_SAVE
        ),
        'mana_filters/filter2_store' => array(
            Mage_Index_Model_Event::TYPE_SAVE
        ),
        Mana_AttributePage_Model_AttributePage_GlobalCustomSettings::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE
        ),
        Mana_AttributePage_Model_AttributePage_StoreCustomSettings::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE,
            Mage_Index_Model_Event::TYPE_DELETE
        ),
        Mana_AttributePage_Model_OptionPage_GlobalCustomSettings::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE,
            Mage_Index_Model_Event::TYPE_DELETE
        ),
        Mana_AttributePage_Model_OptionPage_StoreCustomSettings::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE,
            Mage_Index_Model_Event::TYPE_DELETE
        ),
    );

    protected function _construct() {
        $this->_init('mana_attributepage/optionPage_indexer');
    }

    /**
     * @return Mana_AttributePage_Resource_OptionPage_Indexer
     */
    protected function _getResource() {
        return parent::_getResource();
    }

    /**
     * Register indexer required data inside event object
     *
     * @param   Mage_Index_Model_Event $event
     */
    protected function _registerEvent(Mage_Index_Model_Event $event) {
        if ($event->getEntity() == Mage_Core_Model_Store::ENTITY) {
            if ($event->getData('data_object')->isObjectNew()) {
                $event->addNewData('store_id', $event->getData('data_object')->getId());
            }
        }
        elseif ($event->getEntity() == 'mana_seo/schema/global') {
                $event->addNewData('reindex_all', true);
        }
        elseif ($event->getEntity() == 'mana_seo/schema/store') {
            $event->addNewData('reindex_all', true);
            $event->addNewData('store_id', $event->getData('data_object')->getData('store_id'));
        }
        elseif ($event->getEntity() == Mage_Catalog_Model_Resource_Eav_Attribute::ENTITY) {
            $event->addNewData('attribute_id', $event->getData('data_object')->getId());
        }
        elseif ($event->getEntity() == 'mana_filters/filter2') {
            if ($attributeId = $this->getFilterResource()->getAttributeId($event->getData('data_object'))) {
                $event->addNewData('attribute_id', $attributeId);
            }
        }
        elseif ($event->getEntity() == 'mana_filters/filter2_store') {
            if ($attributeId = $this->getFilterStoreResource()->getAttributeId($event->getData('data_object'))) {
                $event->addNewData('attribute_id', $attributeId);
                $event->addNewData('store_id', $event->getData('data_object')->getData('store_id'));
            }
        }
        elseif ($event->getEntity() == Mana_AttributePage_Model_AttributePage_GlobalCustomSettings::ENTITY) {
            $event->addNewData('attribute_page_global_custom_settings_id', $event->getData('data_object')->getId());
        }
        elseif ($event->getEntity() == Mana_AttributePage_Model_AttributePage_StoreCustomSettings::ENTITY) {
            $event->addNewData('attribute_page_global_id', $event->getData('data_object')->getData('attribute_page_global_id'));
            $event->addNewData('store_id', $event->getData('data_object')->getData('store_id'));
        }
        elseif ($event->getEntity() == Mana_AttributePage_Model_OptionPage_GlobalCustomSettings::ENTITY) {
            if ($event->getType() == Mage_Index_Model_Event::TYPE_DELETE) {
                $event->addNewData('attribute_page_global_id', $event->getData('data_object')->getData('attribute_page_global_id'));
            }
            else {
                $event->addNewData('option_page_global_custom_settings_id', $event->getData('data_object')->getId());
            }
        }
        elseif ($event->getEntity() == Mana_AttributePage_Model_OptionPage_StoreCustomSettings::ENTITY) {
            $event->addNewData('option_page_global_id', $event->getData('data_object')->getData('option_page_global_id'));
            $event->addNewData('store_id', $event->getData('data_object')->getData('store_id'));
        }
    }

    /**
     * Process event based on event state data
     *
     * @param   Mage_Index_Model_Event $event
     */
    protected function _processEvent(Mage_Index_Model_Event $event) {
        $this->_getResource()->process($event->getNewData());
    }

    #region Dependencies

    /**
     * @return Mana_Filters_Resource_Filter2
     */
    public function getFilterResource() {
        return Mage::getResourceSingleton('mana_filters/filter2');
    }

    /**
     * @return Mana_Filters_Resource_Filter2_Store
     */
    public function getFilterStoreResource() {
        return Mage::getResourceSingleton('mana_filters/filter2_store');
    }
    #endregion
}