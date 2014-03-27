<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterDependent
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_FilterDependent_Resource_VirtualColumns  {
    public function addToCollection($select, $result, $columns, $globalEntityName) {
        $this->_add($select, $result, 'main_table', $columns, $globalEntityName);
    }

    public function addToModel($resource, $select, $result, $columns, $globalEntityName) {
        $this->_add($select, $result, $resource->getMainTable(), $columns, $globalEntityName);
    }

    protected function _add($select, $result, $mainTable, $columns, $globalEntityName) {
        if (!$columns || in_array('depends_on_filter_id', $columns)) {
            Mage::helper('mana_db')->joinLeft($select,
                'global', Mage::getSingleton('core/resource')->getTableName($globalEntityName),
                $mainTable.'.global_id = global.id');
            $select->columns("global.depends_on_filter_id AS depends_on_filter_id");
            $result->addColumn('depends_on_filter_id');
        }
    }
}