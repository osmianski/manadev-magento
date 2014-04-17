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
class Mana_AttributePage_Model_Source_PageSort extends Mana_Core_Model_Source_Abstract {
    protected function _getAllOptions() {
        return array(
            array('value' => 'position-asc', 'label' => $this->helper()->__('Position, Ascending')),
            array('value' => 'position-desc', 'label' => $this->helper()->__('Position, Descending')),
            array('value' => 'title-asc', 'label' => $this->helper()->__('Title, Ascending')),
            array('value' => 'title-desc', 'label' => $this->helper()->__('Title, Descending')),
        );
    }

    #region Dependencies
    /**
     * @return Mana_AttributePage_Helper_Data
     */
    public function helper() {
        return Mage::helper('mana_attributepage');
    }
    #endregion
}