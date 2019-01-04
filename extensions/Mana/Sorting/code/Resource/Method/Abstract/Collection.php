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

    public function addUrlKeyFilter($urlKey) {
        $filter = $this->getConnection()->quoteInto("url_key = ?", $urlKey);
        $this->getSelect()->where("$filter OR method_id = ".
            "(SELECT id FROM {$this->getTable('mana_sorting/method')} WHERE $filter)");

        return $this;
    }
}