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
class Mana_Admin_Block_V2_Grid_Column_Form extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text {
    public function render(Varien_Object $row) {
        /* @var $column Mana_Admin_Block_V2_Grid_Column */
        $column = $this->getColumn();
        /* @var $form Mana_Admin_Block_V3_Form */
        $form = $column->getGrid()->getChild($column->getData('form'));
        return
            $form
                ->init('row_'.$row->getId(), $row)
                ->toHtml();
    }
}