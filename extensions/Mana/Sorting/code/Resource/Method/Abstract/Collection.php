<?php
/** 
 * @category    Mana
 * @package     Mana_Sorting
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
abstract class Mana_Sorting_Resource_Method_Abstract_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    public function filterActive() {
        $this->addFieldToFilter('is_active', 1);
    }
}