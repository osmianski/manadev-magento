<?php
/** 
 * @category    Mana
 * @package     Mana_AttributePage
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_AttributePage_Block_Option_Alpha extends Mage_Core_Block_Template {
    protected $_collection;

    /**
     * @return Mana_AttributePage_Block_Option_List
     */
    public function getListBlock() {
        return $this->getParentBlock()->getChild('option_list');
    }

    public function isPagingNeeded() {
        return ($block = $this->getListBlock()) &&
            ($limit = (int)$block->getLimit()) &&
            $this->getCount() > $limit;
    }

    public function getAlphaUrl($alpha) {
        return Mage::getUrl('*/*/*', array(
            '_current' => true,
            '_use_rewrite'=>true,
            '_query' => array(
                'alpha' => $alpha == '#' ? '0' : $alpha,
                'p' => null,
            )
        ));
    }

    public function getAlphaClearUrl() {
        return Mage::getUrl('*/*/*', array(
            '_current' => true,
            '_use_rewrite'=>true,
            '_query' => array(
                'alpha' => null,
                'p' => null,
            )
        ));
    }

    public function getCollection() {
        if (!$this->_collection) {
            $collection = $this->getAttributePage()->getOptionPages();

            // set having products filter
            if ($this->getAttributePage()->getData('hide_empty_option_pages')) {
                $collection->addHavingProductsFilter();
            }

            $this->_collection = $collection;
        }

        return $this->_collection;
    }

    public function getCount() {
        return count($this->getCollection());
    }

    #region Dependencies
    /**
     * @return Mana_AttributePage_Model_AttributePage_Store
     */
    public function getAttributePage() {
        return Mage::registry('current_attribute_page');
    }

    /**
     * @return Mana_Core_Helper_Files
     */
    public function filesHelper() {
        return Mage::helper('mana_core/files');
    }

    /**
     * @return Mana_Core_Helper_Data
     */
    public function coreHelper() {
        return Mage::helper('mana_core');
    }
    #endregion
}