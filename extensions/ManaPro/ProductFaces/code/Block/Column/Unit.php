<?php
/**
 * @category    Mana
 * @package     ManaPro_ProductFaces
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_ProductFaces_Block_Column_Unit extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Select {
    public function render(Varien_Object $row) {
        $html = '<select name="' . ($this->getColumn()->getName() ? $this->getColumn()->getName() : $this->getColumn()->getId()) . '" ' . $this->getColumn()->getValidateClass() . '>';
        $value = $row->getData($this->getColumn()->getIndex());

        if ($row->getEntityId() == 'this') {
            $options = array();
            foreach ($this->getColumn()->getOptions() as $val => $label) {
                if (strpos($val, 'virtual_') !== 0) {
                    $options[$val] = $label;
                }
            }
        }
        else {
            $options = $this->getColumn()->getOptions();
        }
        foreach ($options as $val => $label) {
            $selected = (($val == $value && (!is_null($value))) ? ' selected="selected"' : '');
            $html .= '<option value="' . $val . '"' . $selected . '>' . $label . '</option>';
        }
        $html .= '</select>';
        return $html;
    }
}