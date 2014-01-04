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
class Mana_AttributePage_Model_Source_DescriptionPosition extends Mana_Core_Model_Source_Abstract {
    protected function _getAllOptions() {
        return array(
            array('value' => 'top', 'label' => $this->helper()->__('Top')),
            array('value' => 'bottom', 'label' => $this->helper()->__('Bottom')),
            array('value' => 'hide', 'label' => $this->helper()->__('Hide')),
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