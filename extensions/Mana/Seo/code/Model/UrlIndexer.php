<?php
/**
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Seo_Model_UrlIndexer extends Mana_Core_Model_Indexer {
    protected $_code = 'mana_seo_url';
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
    );

    public function matchEvent(Mage_Index_Model_Event $event) {
        foreach ($this->_getSources() as $source) {
            /* @var $resource Mana_Seo_Resource_UrlIndexer */
            /** @noinspection PhpUndefinedFieldInspection */
            $resource = Mage::getResourceSingleton((string)$source->resource);
            if ($resource->match($this, $event)) {
                return true;
            }
        }

        return parent::matchEvent($event);
    }

    /**
     * Register indexer required data inside event object
     *
     * @param   Mage_Index_Model_Event $event
     */
    protected function _registerEvent(Mage_Index_Model_Event $event) {
        foreach ($this->_getSources() as $source) {
            /* @var $resource Mana_Seo_Resource_UrlIndexer */
            /** @noinspection PhpUndefinedFieldInspection */
            $resource = Mage::getResourceSingleton((string)$source->resource);
            $resource->register($this, $event);
        }
        if ($event->getEntity() == Mage_Core_Model_Store::ENTITY) {
            if ($event->getData('data_object')->isObjectNew()) {
                $event->addNewData('store_id', $event->getData('data_object')->getId());
            }
        }
        elseif ($event->getEntity() == 'mana_seo/schema/global') {
            $event->addNewData('schema_global_id', $event->getData('data_object')->getId());
        }
        elseif ($event->getEntity() == 'mana_seo/schema/store') {
            $event->addNewData('schema_store_id', $event->getData('data_object')->getId());
        }
    }

    /**
     * Process event based on event state data
     *
     * @param   Mage_Index_Model_Event $event
     */
    protected function _processEvent(Mage_Index_Model_Event $event) {
        $this->process($event->getNewData());
    }

    public function reindexAll() {
        $this->process(array(
            "{$this->getCode()}_reindex_all" => true
        ));

        return $this;
    }

    /**
     * @param array $options
     * @return array
     */
    protected function _prepareOptions($options) {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper('mana_core');

        $result = array();
        foreach ($options as $key => $value) {
            if ($core->startsWith($key, $this->getCode() . '_')) {
                $key = substr($key, strlen($this->getCode() . '_'));
            }
            $result[$key] = $value;
        }

        $result = array_merge(array(
            'reindex_all' => false,
        ), $result);

        return $result;
    }

    /**
     * @param array $options
     */
    public function process($options = array()) {
        if (isset($options['store_id'])) {
            $this->getProcess()->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
            return;
        }

        /* @var $resource Mana_Seo_Resource_UrlIndexer_General */
        $resource = Mage::getResourceSingleton('mana_seo/urlIndexer_general');

        $options = $this->_prepareOptions($options);
        //$resource->makeAllRowsObsolete($options);

        foreach ($this->_getSources() as $source) {
            $this->_processSource($source, $options);
        }
        $resource->calculateFinalFields($options);
        $resource->processConflicts($options);
    }

    /**
     * @return Varien_Simplexml_Element[]
     */
    protected function _getSources() {
        /** @noinspection PhpUndefinedFieldInspection */
        return $this->getConfigXml()->sources->children();
    }

    /**
     * @param Varien_Simplexml_Element $source
     * @param array $options
     */
    protected function _processSource($source, $options) {
        /* @var $resource Mana_Seo_Resource_UrlIndexer */
        /** @noinspection PhpUndefinedFieldInspection */
        $resource = Mage::getResourceSingleton((string)$source->resource);
        foreach ($this->_getSchemas() as $schema) {
            if (isset($options['schema_global_id'])) {
                if ($schema->getPrimaryGlobalId() != $options['schema_global_id']) {
                    continue;
                }
            }
            elseif (isset($options['schema_store_id'])) {
                if ($schema->getPrimaryId() != $options['schema_store_id']) {
                    continue;
                }
            }

            $resource->process($this, $schema, $options);
        }
    }

    /**
     * @return Mana_Seo_Model_Schema[]
     */
    protected function _getSchemas() {
        /* @var $dbHelper Mana_Db_Helper_Data */
        $dbHelper = Mage::helper('mana_db');

        /* @var $collection Mana_Db_Resource_Entity_Collection */
        $collection = $dbHelper->getResourceModel('mana_seo/schema/store_flat_collection');

        return $collection;
    }

}