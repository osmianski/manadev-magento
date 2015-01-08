<?php
/** 
 * @category    Mana
 * @package     Mana_Sorting
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Sorting_Model_Method_Indexer extends Mana_Core_Model_Indexer {
    protected $_code = 'mana_sorting_method';
    protected $_matchedEntities = array(
        Mage_Core_Model_Store::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE,
        ),
        Mana_Sorting_Model_Method_StoreCustomSettings::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE,
            Mage_Index_Model_Event::TYPE_DELETE
        ),
    );

    protected function _construct() {
        $this->_init('mana_sorting/method_indexer');
    }

    /**
     * @return Mana_Sorting_Resource_Method_Indexer
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
        elseif ($event->getEntity() == Mana_Sorting_Model_Method_StoreCustomSettings::ENTITY) {
            $event->addNewData('method_id', $event->getData('data_object')->getData('method_id'));
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