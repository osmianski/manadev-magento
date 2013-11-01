<?php
/**
 * @category    Mana
 * @package     Mana_Db
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* BASED ON SNIPPET: Models/Observer */
/**
 * This class observes certain (defined in etc/config.xml) events in the whole system and provides public methods - handlers for
 * these events.
 * @author Mana Team
 *
 */
class Mana_Db_Model_Observer {
	protected function _getProcess() {
		return Mage::getModel('index/process')->load('mana_db_replicator', 'indexer_code');
	}
	/* BASED ON SNIPPET: Models/Event handler */
	/**
	 * Catches moment after database upgrade to rerun data replication actions (handles events 
	 * "controller_action_predispatch", "core_config_data_save_commit_after")
	 * @param Varien_Event_Observer $observer
	 */
	public function replicateAllIfPending($observer) {
		if (Mage::registry('m_run_db_replication')) {
			Mage::unregister('m_run_db_replication');
			$this->_getProcess()->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX)->reindexAll();
		}

        /* @var $indexer Mage_Index_Model_Indexer */
        $indexer = Mage::getSingleton('index/indexer');

        if ($reindex = Mage::registry('m_reindex')) {
            foreach ($reindex as $code) {
                $indexer->getProcessByCode($code)
                    ->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX)
                    ->reindexAll();
            }
        }
	    if ($savedObjects = Mage::registry('m_objects_to_be_saved')) {
            Mage::unregister('m_objects_to_be_saved');
            foreach ($savedObjects as $object) {
                Mage::dispatchEvent('m_saved', compact('object'));
            }
        }

    }
	/**
	 * Enter description here ...
	 * @param unknown_type $observer
	 * @deprecated
	 */
	public function afterUpgrade($observer) {
	}
	/* BASED ON SNIPPET: Models/Event handler */
	/**
	 * Runs data replication store-creation related actions (handles event "store_save_commit_after")
	 * @param Varien_Event_Observer $observer
	 */
	public function afterStoreSave($observer) {
		$dataObject = $observer->getEvent()->getDataObject();
		
		if (!$dataObject->getdata('_m_prevent_replication')) {
			Mage::helper('mana_db')->replicate(array(
				'trackKeys' => true,
				'filter' => array('core/store' => array('saved' => array($dataObject->getId()))),
			));
		}
	}
	/* BASED ON SNIPPET: Models/Event handler */
	/**
	 * Runs data replication store-deletion related actions (handles event "store_delete_commit_after")
	 * @param Varien_Event_Observer $observer
	 */
	public function afterStoreDelete($observer) {
		$dataObject = $observer->getEvent()->getDataObject();
		
		if (!$dataObject->getdata('_m_prevent_replication')) {
			Mage::helper('mana_db')->replicate(array(
				'trackKeys' => true,
				'filter' => array('core/store' => array('deleted' => array($dataObject->getId()))),
			));
		}
	}
	/* BASED ON SNIPPET: Models/Event handler */
	/**
	 * Runs data replication attribute-editing related actions (handles event "catalog_entity_attribute_save_commit_after")
	 * @param Varien_Event_Observer $observer
	 */
	public function afterCatalogAttributeSave($observer) {
		$dataObject = $observer->getEvent()->getDataObject();
		
		if (!$dataObject->getdata('_m_prevent_replication')) {
			Mage::helper('mana_db')->replicate(array(
				'trackKeys' => true,
				'filter' => array('eav/attribute' => array('saved' => array($dataObject->getId()))),
			));
		}
	}
	/* BASED ON SNIPPET: Models/Event handler */
	/**
	 * Runs data replication attribute-deletion related actions (handles event "catalog_entity_attribute_delete_commit_after")
	 * @param Varien_Event_Observer $observer
	 */
	public function afterCatalogAttributeDelete($observer) {
		$dataObject = $observer->getEvent()->getDataObject();
		
		if (!$dataObject->getdata('_m_prevent_replication')) {
			Mage::helper('mana_db')->replicate(array(
				'trackKeys' => true,
				'filter' => array('eav/attribute' => array('deleted' => array($dataObject->getId()))),
			));
		}
	}
	/* BASED ON SNIPPET: Models/Event handler */
	/**
	 * Runs data replication actions if related configuration changed (handles event "core_config_data_save_commit_after")
	 * @param Varien_Event_Observer $observer
	 */
	public function afterSaveCommit($observer) {
		$object = $observer->getEvent()->getObject();
		if ($object->getResourceName() == 'core/config_data' &&
			Mage::helper('mana_db')->isReplicatedConfigChanged($object))
		{
			Mage::register('m_run_db_replication', true);
		}
		elseif ($object->getResourceName() == 'cms/page') {
            if (!Mage::registry('m_prevent_indexing_on_save')) {
                $this->getIndexerSingleton()->processEntityAction($object, 'cms/page', Mage_Index_Model_Event::TYPE_SAVE);
            }
        }
	}

    public function afterConfigSave($observer) {}

    #region Dependencies
    /**
     * @return Mage_Index_Model_Indexer
     */
    public function getIndexerSingleton() {
        return Mage::getSingleton('index/indexer');
    }
    #endregion
}