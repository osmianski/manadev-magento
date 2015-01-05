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
    public $_outOfStock;
    /**
     * @return Varien_Simplexml_Element[]
     */
    public function getSortingMethodXmls() {
        if (!$this->_sortingMethodXmls) {
            $result = array();
            foreach ($this->coreHelper()->getSortedXmlChildren(Mage::getConfig()->getNode(), 'mana_sorting') as $code => $xml) {
                if (Mage::getStoreConfigFlag('mana_sorting/' . $code . '/enabled')) {
                    $xml->code = $code;
                    $result[$code] = $xml;
                }
            }
            uksort($result, array($this, '_compareSortingMethodByPosition'));
            $this->_sortingMethodXmls = $result;
        }
        return $this->_sortingMethodXmls;
    }

    public function getOutOfStockOption () {
        if (!$this->_outOfStock) {
            return $this->_outOfStock = Mage::getStoreConfigFlag('mana_sorting/out_of_stock/enabled');
        }
        return $this->_outOfStock;
    }

    protected function _compareSortingMethodByPosition($a, $b) {
        $aPos = Mage::getStoreConfig('mana_sorting/' . $a . '/position');
        $bPos = Mage::getStoreConfig('mana_sorting/' . $b . '/position');

        if ($aPos - $bPos > 0) return 1;
        if ($aPos - $bPos < 0) return -1;
        return 0;
    }

    public function addManaSortingOptions($options) {
        foreach ($this->getSortingMethodXmls() as $xml) {
            $options[] = array(
                'label' => (string)$xml->label,
                'value' => (string)$xml->code
            );
        }
        return $options;
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