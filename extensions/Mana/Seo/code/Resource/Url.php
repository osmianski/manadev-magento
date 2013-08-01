<?php
/** 
 * @category    Mana
 * @package     Mana_Seo
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Seo_Resource_Url extends Mana_Db_Resource_Entity {
    protected function _getLoadSelect($field, $value, $object) {
        $select = parent::_getLoadSelect($field, $value, $object);
        $select->
            joinInner(array('schema' => $this->getTable('mana_seo/schema_store_flat')),
                "`schema`.`id` = {$this->getMainTable()}.`schema_id`", array('global_schema_id' => 'global_id', 'store_id'));
        return $select;
    }
}