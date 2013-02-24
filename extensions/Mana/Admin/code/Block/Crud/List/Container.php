<?php
/**
 * @category    Mana
 * @package     Mana_Admin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Enter description here ...
 * @author Mana Team
 *
 */
class Mana_Admin_Block_Crud_List_Container extends Mage_Adminhtml_Block_Widget_Container {
    /**
     * Set template
     */
    public function __construct() {
        parent::__construct();
        $this->setTemplate('mana/admin/container.phtml');
    }
}