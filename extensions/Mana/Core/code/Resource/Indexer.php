<?php
/** 
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
abstract class Mana_Core_Resource_Indexer extends Mage_Core_Model_Mysql4_Abstract {
    public function insert($tableName, $fields = array(), $onDuplicate = true) {
        $sql = "INSERT INTO `{$tableName}` ";
        $sql .= "(`" . implode('`,`', array_keys($fields)) . "`) ";
        $sql .= "VALUES (" . implode(',', $fields) . ") ";

        if ($onDuplicate && $fields) {
            $sql .= " ON DUPLICATE KEY UPDATE";
            $updateFields = array();
            foreach ($fields as $key => $field) {
                $key = $this->_getWriteAdapter()->quoteIdentifier($key);
                $updateFields[] = "{$key}=VALUES({$key})";
            }
            $sql .= " " . implode(', ', $updateFields);
        }

        return $sql;
    }
}