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
class Mana_Admin_Block_Crud_List_Grid extends Mana_Admin_Block_Crud_Grid {
    public function __construct() {
        parent::__construct();
        $this->setSaveParametersInSession(true);
    }
    public function getGridUrl() {
        return Mage::helper('mana_admin')->getStoreUrl('*/*/grid');
    }

    public function getRowUrl($row) {
	    return Mage::helper('mana_admin')->getStoreUrl('*/*/edit', array(
	    	'id' => Mage::helper('mana_admin')->isGlobal() ? $row->getId() : $row->getGlobalId(),
	    ));
    }
}