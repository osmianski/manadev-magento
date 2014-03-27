<?php
/** 
 * @category    Mana
 * @package     Mana_Menu
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Menu_Block_Tree_Item extends Mage_Core_Block_Template {
    protected function _construct() {
        $this->setTemplate('mana/menu/tree/item.phtml');
        parent::_construct();
    }
}