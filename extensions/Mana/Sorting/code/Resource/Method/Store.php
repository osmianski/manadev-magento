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
class Mana_Sorting_Resource_Method_Store extends Mana_Sorting_Resource_Method_Abstract {
    protected function _construct() {
        $this->_init(Mana_Sorting_Model_Method_Store::ENTITY, 'id');
    }

    protected function _getLoadSelect($field, $value, $object) {
        if (!$object->getData('store_id')) {
            throw new Exception($this->coreHelper()->__(
                "You must call setData('store_id', ...) before calling load() on %s objects.",
                get_class($object)));
        }
        $db = $this->_getReadAdapter();
        $select = $db->select()
            ->from(array('main_table' => $this->getMainTable()));

        $select
            ->where("`main_table`.`$field`=?", $value)
            ->where("`main_table`.`store_id`=?", $object->getData('store_id'));
        return $select;
    }
}