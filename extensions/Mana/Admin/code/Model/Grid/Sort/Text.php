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
class Mana_Admin_Model_Grid_Sort_Text {
    /**
     * @param Mana_Db_Resource_Entity_Collection $collection
     * @param string $column
     * @param string $dir
     * @return Mana_Admin_Model_Grid_Sort_Text
     */
    public function setOrder($collection, $column, $dir) {
        $collection->setOrder($column, $dir);

        return $this;
    }
}