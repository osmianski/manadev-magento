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

    protected $_matchedEntities = array(
        Mage_CatalogInventory_Model_Stock_Item::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE
        ),
    );

    /**
     * Register indexer required data inside event object
     *
     * @param   Mage_Index_Model_Event $event
     */
    protected function _registerEvent(Mage_Index_Model_Event $event) {
        /* @var $object Mage_CatalogInventory_Model_Stock_Item */
        $object      = $event->getDataObject();

        $event->addNewData('product_id', $object->getProductId());
    }

    /**
     * Process event based on event state data
     *
     * @param   Mage_Index_Model_Event $event
     */
    protected function _processEvent(Mage_Index_Model_Event $event) {
        foreach ($this->getXml()->types->children() as $typeXml) {
            /* @var $type ManaPro_FilterAttributes_Resource_Type */
            $type = Mage::getResourceSingleton((string)$typeXml->resource);

            $type->process($this, $event->getNewData());
        }
    }

    /**
     * @return Mage_Index_Model_Process
     */
    protected function _getProductAttributesProcess() {
		return Mage::getModel('index/process')->load('catalog_product_attribute', 'indexer_code');
	}
	/* BASED ON SNIPPET: Models/Event handler */
	/**
	 * Catches moment after database upgrade to rerun data replication actions (handles events
	 * "controller_action_predispatch", "core_config_data_save_commit_after")
	 * @param Varien_Event_Observer $observer
	 */
    public function reindexAll() {
        foreach ($this->getXml()->types->children() as $typeXml) {
            /* @var $type ManaPro_FilterAttributes_Resource_Type */
            $type = Mage::getResourceSingleton((string)$typeXml->resource);

            $type->process($this, array());
        }
        $this->_getProductAttributesProcess()->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX)->reindexAll();


        return $this;
    }

}