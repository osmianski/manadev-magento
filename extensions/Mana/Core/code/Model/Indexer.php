<?php
/** 
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
abstract class Mana_Core_Model_Indexer extends Mage_Index_Model_Indexer_Abstract {
    protected $_code;
    protected $_process;
    protected $_xml = null;
    protected $_configXml = null;

    public function getCode() {
        return $this->_code;
    }

    /**
     * @return Mage_Index_Model_Process | bool
     */
    public function getProcess() {
        if (!$this->_process) {
            $this->_process = Mage::getModel('index/process')->load($this->getCode(), 'indexer_code');
        }

        return $this->_process;
    }

    /**
     * @return Varien_Simplexml_Element | bool
     */
    public function getXml() {
        if (is_null($this->_xml)) {
            $result = Mage::getConfig()->getXpath("//global/index/indexer/{$this->getProcess()->getIndexerCode()}");

            $this->_xml = count($result) == 1 ? $result[0] : false;
        }
        return $this->_xml;
    }

    public function getConfigXml() {
        if (is_null($this->_configXml)) {
            $result = Mage::getConfig()->getXpath("//global/index/indexer_config/{$this->getProcess()->getIndexerCode()}");

            $this->_configXml = count($result) == 1 ? $result[0] : false;
        }
        return $this->_configXml;
    }

    /**
     * Get Indexer name
     *
     * @return string
     */
    public function getName() {
        /** @noinspection PhpUndefinedFieldInspection */
        return (string)$this->getXml()->name;
    }

    /**
     * Retrieve Indexer description
     *
     * @return string
     */
    public function getDescription() {
        /** @noinspection PhpUndefinedFieldInspection */
        return (string)$this->getXml()->description;
    }
}