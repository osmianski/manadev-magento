<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterAttributes
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
abstract class ManaPro_FilterAttributes_Resource_Type extends Mage_Core_Model_Mysql4_Abstract {
    /**
     * Resource initialization
     */
    protected function _construct() {
        $this->_setResource('core');
    }

    /**
     * @param ManaPro_FilterAttributes_Model_Indexer $indexer
     * @param array $options
     */
    abstract public function process($indexer, $options);

    /**
     * @param $attributeCode
     * @return array | bool
     */
    protected function _getAttributeByCode ( $attributeCode) {
        $db = $this->_getWriteAdapter();

        $select = $db->select()
            ->from(array('a' => $this->getTable('eav/attribute')), array('attribute_id', 'backend_type', 'backend_table'))
            ->where("`a`.`attribute_code` = ?", $attributeCode);

        return $db->fetchRow($select);
    }

    /**
     * @param $fieldExpr
     * @param $values
     * @return string
     */
    protected function _getIfExpr($fieldExpr, $values) {
        $result = "''";

        foreach ($values as $source => $target) {
            $result = "IF($fieldExpr = $source, '$target', $result)";
        }
        return $result;
    }
}