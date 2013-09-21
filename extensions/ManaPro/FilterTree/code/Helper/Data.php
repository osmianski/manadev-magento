<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterTree
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * Generic helper functions for ManaPro_FilterTree module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class ManaPro_FilterTree_Helper_Data extends Mage_Core_Helper_Abstract {
    protected $_rootCategory;

    /**
     * @return Mage_Catalog_Model_Category
     */
    public function getRootCategory() {
        if (!$this->_rootCategory) {
            $this->_rootCategory = Mage::getModel('catalog/category')->load(
                Mage::app()->getStore()->getRootCategoryId());
        }
        return $this->_rootCategory;
    }
}