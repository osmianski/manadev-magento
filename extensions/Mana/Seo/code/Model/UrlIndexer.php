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
        $options = $this->_prepareOptions($options);
        $this->_makeAllRowsObsolete($options);
        foreach ($this->_getSources() as $source) {
            $this->_processSource($source, $options);
        }
        $this->_processConflicts($options);
    }

    /**
     * @return Varien_Simplexml_Element[]
     */
    protected function _getSources() {
        /** @noinspection PhpUndefinedFieldInspection */
        return $this->getXml()->sources->children();
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
            $resource->process($this, $schema, $options);
        }
    }

    protected function _processConflicts($options) {
    }

    protected function _makeAllRowsObsolete($options) {
        /* @var $resource Mana_Seo_Resource_UrlIndexer_General */
        $resource = Mage::getResourceSingleton('mana_seo/urlIndexer_general');
        $resource->makeAllRowsObsolete($options);

    }

    /**
     * @return Mana_Seo_Model_Schema[]
     */
    protected function _getSchemas() {
        /* @var $dbHelper Mana_Db_Helper_Data */
        $dbHelper = Mage::helper('mana_db');

        /* @var $collection Mana_Db_Resource_Entity_Collection */
        $collection = $dbHelper->getResourceSingleton('mana_seo/schema/store_flat_collection');

        return $collection;
    }

}