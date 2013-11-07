<?php
/** 
 * @category    Mana
 * @package     Mana_Menu
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
abstract class Mana_Menu_Block_Abstract extends Mage_Core_Block_Template {
    /**
     * @var Mage_Core_Model_Config_Base
     */
    protected $_baseXml;
    /**
     * @var Mage_Core_Model_Config_Element
     */
    protected $_xml;
    protected $_xmlInitialized;

    protected function _prepareLayout() {
        Mage::helper('mana_core/layout')->delayPrepareLayout($this);

        return parent::_prepareLayout();
    }

    public function delayedPrepareLayout() {
        return $this;
    }

    public function getXml() {
        if (!$this->_xmlInitialized) {
            if ($xml = $this->_getData('xml')) {
                $this->_baseXml = new Mage_Core_Model_Config_Base();
                $this->_baseXml->loadString($xml);
                $this->_xml = $this->_baseXml->getNode();
                $this->_processGenerators();
            }
            $this->_xmlInitialized = true;
        }

        return $this->_xml;
    }

    protected function _processGenerators() {
        foreach ($this->_xml->xpath('//*[generators]') as $elementXml) {
            foreach ($elementXml->generators->children() as $generatorXml) {
                /* @var $generator Mana_Menu_Model_Generator */
                $generator = Mage::getModel((string)$generatorXml->model);
                $generator->extend($elementXml);
            }
        }
    }
}