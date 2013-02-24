<?php
/**
 * @category    Mana
 * @package     Mana_AttributePage
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_AttributePage_Block_Adminhtml_New_Container extends Mana_Admin_Block_Crud_List_Container {
    public function __construct() {
        parent::__construct();
        $this->_headerText = $this->__("Enable 'Shop By' Feature For Attribute");
    }
}