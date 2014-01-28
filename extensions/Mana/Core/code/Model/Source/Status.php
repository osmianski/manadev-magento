<?php
/** 
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Core_Model_Source_Status extends Mana_Core_Model_Source_Abstract {
    protected function _getAllOptions() {
        return array(
            array('value' => 1, 'label' => $this->helper()->__('Enabled')),
            array('value' => 0, 'label' => $this->helper()->__('Disabled')),
        );
    }

    #region Dependencies
    /**
     * @return Mana_AttributePage_Helper_Data
     */
    public function helper() {
        return Mage::helper('mana_core');
    }
    #endregion
}