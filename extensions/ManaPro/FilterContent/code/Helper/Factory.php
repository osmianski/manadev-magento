<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterContent
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_FilterContent_Helper_Factory extends Mage_Core_Helper_Abstract {
    protected $_blockHelpers = array();
    protected $_content = array();
    protected $_allContentCreated = false;
    protected $_actionSources;

    /**
     * @param string $key
     * @param string $helperClass
     * @return bool|ManaPro_FilterContent_Helper_Block
     * @throws Exception
     */
    public function createBlockHelper($key, $helperClass) {
        if ($this->helper()->isContentKey($key)) {
            if (!isset($this->_blockHelpers[$helperClass])) {
                $helper = Mage::helper($helperClass);
                if (!($helper instanceof ManaPro_FilterContent_Helper_Block)) {
                    throw new Exception(sprintf('%1 must be instance of %2', get_class($helper), 'ManaPro_FilterContent_Helper_Block'));
                }
                $this->_blockHelpers[$helperClass] = $helper;
            }
            return $this->_blockHelpers[$helperClass];
        }
        else {
            return false;
        }
    }

    /**
     * @param string $key
     * @return ManaPro_FilterContent_Model_Content
     * @throws Exception
     */
    public function getContent($key) {
        if (!isset($this->_content[$key])) {
            $xml = $this->helper()->getContentXml($key);

            /* @var $content ManaPro_FilterContent_Model_Content */
            $content = Mage::getModel('manapro_filtercontent/content');
            $content->init($xml);
            $this->_content[$key] = $content;
        }
        return $this->_content[$key];
    }

    /**
     * @return ManaPro_FilterContent_Model_Content[]
     */
    public function getAllContent() {
        if (!$this->_allContentCreated) {
            $content = array();
            foreach ($this->helper()->getAllContentXmls() as $key => $xml) {
                $content[$key] = $this->getContent($key);
            }

            $this->_allContentCreated = true;
            $this->_content = $content;
        }
        return $this->_content;
    }

    /**
     * @param Mage_Core_Model_Config_Element $xml
     * @return ManaPro_FilterContent_Helper_Action
     * @throws Exception
     */
    protected function _createAction($xml) {
        $helperClass = (string)($xml->helper);

        /* @var $helper ManaPro_FilterContent_Helper_Action */
        $helper = Mage::helper($helperClass);
        $helper->init($xml);
        if (!($helper instanceof ManaPro_FilterContent_Helper_Action)) {
            throw new Exception(sprintf('%1 must be instance of %2', get_class($helper), 'ManaPro_FilterContent_Helper_Action'));
        }

        return $helper;
    }

    /**
     * @return ManaPro_FilterContent_Helper_Action[]
     */
    public function getActions() {
        if (!$this->_actionSources) {
            $this->_actionSources = array();
            foreach ($this->helper()->getAllActionXmls() as $key => $xml) {
                $this->_actionSources[$key] = $this->_createAction($xml);
            }

        }
        return $this->_actionSources;
    }

    #region Dependencies
    /**
     * @return ManaPro_FilterContent_Helper_Data
     */
    public function helper() {
        return Mage::helper('manapro_filtercontent');
    }

    #endregion
}