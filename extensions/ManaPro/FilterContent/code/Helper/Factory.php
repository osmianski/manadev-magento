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
    protected $_contentHelpers = array();
    protected $_allContentHelpersCreated = false;

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
     * @return ManaPro_FilterContent_Helper_Content
     * @throws Exception
     */
    public function createContentHelper($key) {
        if (!isset($this->_contentHelpers[$key])) {
            $helperClass = (string)($this->helper()->getContentHelperXml($key)->helper);
            $helper = Mage::helper($helperClass);
            if (!($helper instanceof ManaPro_FilterContent_Helper_Content)) {
                throw new Exception(sprintf('%1 must be instance of %2', get_class($helper), 'ManaPro_FilterContent_Helper_Content'));
            }
            $this->_contentHelpers[$key] = $helper;
        }
        return $this->_contentHelpers[$key];
    }

    /**
     * @return ManaPro_FilterContent_Helper_Content[]
     */
    public function getAllContentHelpers() {
        if (!$this->_allContentHelpersCreated) {
            $helpers = array();
            foreach ($this->helper()->getAllContentHelperXmls() as $key => $xml) {
                $helpers[$key] = $this->createContentHelper($key);
            }

            $this->_allContentHelpersCreated = true;
            $this->_contentHelpers = $helpers;
        }
        return $this->_contentHelpers;
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