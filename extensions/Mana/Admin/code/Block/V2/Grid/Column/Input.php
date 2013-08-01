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
class Mana_Admin_Block_V2_Grid_Column_Input extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {
    public function render(Varien_Object $row) {
        $html = '<input type="text" ';
        $html .= 'name="' . $this->getColumn()->getId() . '" ';
        $html .= 'value="' . htmlspecialchars($row->getData($this->getColumn()->getIndex())) . '"';
        $html .= 'class="input-text ' . $this->getColumn()->getInlineCss() . '"/>';

        return $html;
    }
}