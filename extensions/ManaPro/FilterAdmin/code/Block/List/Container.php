<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterAdmin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Enter description here ...
 * @author Mana Team
 *
 */
class ManaPro_FilterAdmin_Block_List_Container extends Mana_Admin_Block_Crud_List_Container {
    public function __construct() {
        parent::__construct();
        $this->_headerText = $this->__('Layered Navigation Filters');
    }
}