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
class Mana_Admin_Block_Placeholder extends Mage_Adminhtml_Block_Template {
    protected function _construct() {
        $this->setTemplate('mana/admin/placeholder.phtml');
    }
}