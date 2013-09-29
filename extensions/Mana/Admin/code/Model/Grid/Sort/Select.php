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
class Mana_Admin_Model_Grid_Sort_Select {
    /**
     * @param Mana_Db_Resource_Entity_Collection $collection
     * @param string $column
     * @param string $dir
     * @return Mana_Admin_Model_Grid_Sort_Select
     */
    public function setOrder($collection, $column, $dir) {
        // TD: add CASE WHEN THEN column with option labels and sort by it.
        // TD: Alternatively, check if options is a model and if that model returns database columns and table join
        //  information - in this case join a column and sort by it
        $collection->setOrder($column, $dir);

        return $this;
    }
}