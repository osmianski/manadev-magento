<?php
/** 
 * @category    Mana
 * @package     Mana_Sorting
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * Generic helper functions for Mana_Sorting module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class Mana_Sorting_Helper_Data extends Mage_Core_Helper_Abstract {
    protected $_sortingMethodXmls;
    /**
     * @return Varien_Simplexml_Element[]
     */
    public function getSortingMethodXmls() {
        if (!$this->_sortingMethodXmls) {
            $result = array();
            foreach ($this->coreHelper()->getSortedXmlChildren(Mage::getConfig()->getNode(), 'mana_sorting') as $code => $xml) {
                $xml->code = $code;
                $result[$code] = $xml;
            }
            $this->_sortingMethodXmls = $result;
        }
        return $this->_sortingMethodXmls;
    }

    #region Dependencies
    /**
     * @return Mana_Core_Helper_Data
     */
    public function coreHelper() {
        return Mage::helper('mana_core');
    }
    #endregion
}