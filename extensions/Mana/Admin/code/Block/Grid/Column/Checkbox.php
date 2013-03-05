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
class Mana_Admin_Block_Grid_Column_Checkbox extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Checkbox {
    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row) {
        $value = $row->getData($this->getColumn()->getIndex());
        $checked = $value ? ' checked="checked"' : '';

        return $this->_getCheckboxHtml(1, $checked);
    }
}