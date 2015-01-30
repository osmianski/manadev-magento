<?php
/** 
 * @category    Mana
 * @package     Mana_Content
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Content_Model_Page_Indexer extends Mana_Core_Model_Indexer {
    protected $_code = 'mana_content_page';
    protected $_matchedEntities = array(
        Mage_Core_Model_Store::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE,
        ),
        Mana_Content_Model_Page_GlobalCustomSettings::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE
        ),
        Mana_Content_Model_Page_StoreCustomSettings::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE,
            Mage_Index_Model_Event::TYPE_DELETE
        ),
    );

    protected function _construct() {
        $this->_init('mana_content/page_indexer');
    }

    /**
     * @return Mana_Content_Resource_Page_Indexer
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
        elseif ($event->getEntity() == Mana_Content_Model_Page_GlobalCustomSettings::ENTITY) {
            $event->addNewData('page_global_custom_settings_id', $event->getData('data_object')->getId());
        }
        elseif ($event->getEntity() == Mana_Content_Model_Page_StoreCustomSettings::ENTITY) {
            $event->addNewData('page_global_id', $event->getData('data_object')->getData('page_global_id'));
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
}