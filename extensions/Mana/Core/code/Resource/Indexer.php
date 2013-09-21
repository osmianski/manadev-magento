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
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper('mana_core');

        return $core->insert($this->_getWriteAdapter(), $tableName, $fields, $onDuplicate);
    }
}