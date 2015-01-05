<?php
/** 
 * @category    Mana
 * @package     Mana_Sorting
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Sorting_Rewrite_Category_Source_SortBy extends Mage_Catalog_Model_Category_Attribute_Source_Sortby  {
    public function getAllOptions()
    {
        if (is_null($this->_options)) {
            $options = parent::getAllOptions();
            $this->sortingHelper()->addManaSortingOptions($options);
            $this->_options = $options;
        }
        return $this->_options;
    }

    #region Dependencies
    /**
     * @return Mana_Sorting_Helper_Data
     */
    public function sortingHelper() {
        return Mage::helper('mana_sorting');
    }

    #endregion
}