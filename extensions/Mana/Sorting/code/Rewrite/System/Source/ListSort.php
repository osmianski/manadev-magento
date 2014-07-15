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
class Mana_Sorting_Rewrite_System_Source_ListSort extends Mage_Adminhtml_Model_System_Config_Source_Catalog_ListSort {
    public function toOptionArray()
    {
        return array_merge(parent::toOptionArray(), array(
            array(
                'label' => $this->helper()->__('Nash'),
                'value' => 'nash'
            ),
            array(
                'label' => $this->helper()->__('VO'),
                'value' => 'vo'
            ),
        ));
    }

    #region Dependencies

    /**
     * @return Mana_Sorting_Helper_Data
     */
    public function helper() {
        return Mage::helper('mana_sorting');
    }
    #endregion
}