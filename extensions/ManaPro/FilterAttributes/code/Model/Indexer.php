<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterAttributes
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_FilterAttributes_Model_Indexer  extends Mana_Core_Model_Indexer {
    protected $_code = 'manapro_filterattributes';

    /**
     * Register indexer required data inside event object
     *
     * @param   Mage_Index_Model_Event $event
     */
    protected function _registerEvent(Mage_Index_Model_Event $event) {
        // TODO: Implement _registerEvent() method.
    }

    /**
     * Process event based on event state data
     *
     * @param   Mage_Index_Model_Event $event
     */
    protected function _processEvent(Mage_Index_Model_Event $event) {
        // TODO: Implement _processEvent() method.
    }

    public function reindexAll() {
        foreach ($this->getXml()->types->children() as $typeXml) {
            /* @var $type ManaPro_FilterAttributes_Resource_Type */
            $type = Mage::getResourceSingleton((string)$typeXml->resource);

            $type->process($this, array());
        }
        return $this;
    }

}