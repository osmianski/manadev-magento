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
class Mana_AttributePage_Model_Source_AttributePageTemplate extends Mana_Core_Model_Source_Abstract {
    protected function _getAllOptions() {
        return array(
            array('value' => 'template1', 'label' => $this->helper()->__('Template 1')),
            array('value' => 'template2', 'label' => $this->helper()->__('Template 2')),
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