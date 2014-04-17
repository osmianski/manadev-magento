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
class Mana_AttributePage_Model_Source_ShowAll extends Mana_Core_Model_Source_Abstract {
    protected function _getAllOptions() {
        return array(
            array('value' => 'never', 'label' => $this->helper()->__('Never')),
            array('value' => 'always', 'label' => $this->helper()->__('Always')),
            array('value' => 'if-max-reached', 'label' => $this->helper()->__('If reached maximum number of menu links')),
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