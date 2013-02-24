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
class Mana_Admin_Block_Crud_Detail_Grid extends Mana_Admin_Block_Crud_Grid {
    public function getRowUrl($row) {
	    return '';
    }
    public function getRowClass($row) {
    	return 'r-'.$row->getId();
    }
}