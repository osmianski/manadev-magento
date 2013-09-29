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
class Mana_Admin_Block_Grid_Filter_Checkbox extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Checkbox {
    public function getCondition() {
        if ($this->getValue()) {
            return 1;
        }
        else {
            return array(
                array('eq' => 0),
                array('is' => new Zend_Db_Expr('NULL'))
            );
        }
    }
}