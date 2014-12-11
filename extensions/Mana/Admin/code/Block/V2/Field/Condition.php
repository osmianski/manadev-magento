<?php
/** 
 * @category    Mana
 * @package     Mana_Admin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Admin_Block_V2_Field_Condition extends Mana_Admin_Block_V3_Field {
    public function render(Varien_Data_Form_Element_Abstract $element) {
        $this->_element = $element;
        if ($conditions = $this->getFlatModel()->getConditions()) {
            /* @var $conditions Mage_Rule_Model_Condition_Combine */
            return $conditions->asHtmlRecursive();
        }

        return '';
    }

    #region Dependencies
    /**
     * @return Mage_Rule_Model_Rule
     */
    public function getFlatModel() {
        return $this->getForm()->getData('flat_model');
    }
    #endregion
}