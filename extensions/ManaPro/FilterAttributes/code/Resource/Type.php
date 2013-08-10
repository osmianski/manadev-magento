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
        $db = $this->_getReadAdapter();

        $select = $db->select()
            ->from(array('a' => $this->getTable('eav/attribute')), array('attribute_id', 'backend_type', 'backend_table'))
            ->where("`a`.`attribute_code` = ?", $attributeCode);

        return $db->fetchRow($select);
    }

    protected function _getAttributeValues ( $attributeId) {
        $db = $this->_getReadAdapter();

        $select = $db->select()
            ->from(array('o' => $this->getTable('eav/attribute_option')), array('sort_order' => new Zend_Db_Expr('@curRow := @curRow + 1'), 'option_id'))
            ->where("`o`.`attribute_id` = ?", $attributeId)
            -> join(array('s' =>  new Zend_Db_Expr('(SELECT @curRow := 0)')), null, null)
            ->order('sort_order ' .  Zend_Db_Select::SQL_DESC);;

        return $db->fetchAll($select);
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

    protected function _getIfExprBySortOrderDesc($fieldExpr, $values) {
        foreach ($values as $source => $target) {

            $sort_order = $target["sort_order"];
            $option_id = $target["option_id"];
            if (empty($result))
                {
                    $result = "'$option_id'";
                }
            $result = "IF($fieldExpr = $sort_order, '$option_id', $result)";
        }

        return $result;
    }

}