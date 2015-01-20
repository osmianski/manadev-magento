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
class Mana_Sorting_Model_Source_Direction extends Mana_Core_Model_Source_Abstract
{
    protected function _getAllOptions() {
        $result = array(
            array('value' => '1', 'label' => $this->coreHelper()->__('Ascending')),
            array('value' => '0', 'label' => $this->coreHelper()->__('Descending')),
        );
        return $result;
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