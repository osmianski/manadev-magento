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
class Mana_Admin_Block_V2_Form_Field_SelectText extends Varien_Data_Form_Element_Select {
    public function getElementHtml() {
        $values = $this->getData('values');
        $selected = $this->getData('value');
        if (($index = $this->coreHelper()->arrayFind($values, 'value', $selected)) !== false) {
            $html = $this->_escape($values[$index]['label']);
        }
        else {
            $html = '';
        }
        if ($this->getData('bold')) {
            $html = "<strong data-value=\"$selected\">$html</strong>";
        }
        else {
            $html = "<span data-value=\"$selected\">$html</span>";
        }
        return $html;
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