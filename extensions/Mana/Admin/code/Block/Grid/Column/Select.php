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
class Mana_Admin_Block_Grid_Column_Select extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Select {
    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row) {
        $html = '<select name="' . ($this->getColumn()->getName() ? $this->getColumn()->getName() : $this->getColumn()->getId()) . '" ' . $this->getColumn()->getValidateClass() . '>';
        $value = $row->getData($this->getColumn()->getIndex());

        $options = $this->getColumn()->getOptions();
        if (is_string($options)) {
            /* @var $optionSource Mana_Core_Model_Source_Abstract */
            $optionSource = Mage::getModel($options);
            $options = $optionSource->getOptionArray();
        }
        foreach ($options as $val => $label) {
            $selected = (($val == $value && (!is_null($value))) ? ' selected="selected"' : '');
            $html .= '<option value="' . $val . '"' . $selected . '>' . $label . '</option>';
        }
        $html .= '</select>';

        return $html;
    }
}