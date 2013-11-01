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

        $select =
        'SELECT `g` . `sort_order`, GROUP_CONCAT(`g` . `option_id`) AS option_id FROM(
           SELECT
              if (`o`.`sort_order` = 4, 0, if (`o`.`option_id` <> @option_id,
              @rn := 4 -`o`.`sort_order` + least(0, @option_id := `o`.`option_id`),
              @rn := @rn + 1)) sort_order,
              (@option_id := `o`.`option_id`) AS option_id
            FROM `' . $this->getTable('eav/attribute_option') .'` AS `o`
              INNER JOIN `' . $this->getTable('eav/attribute_option') .'` AS `o2`
                ON `o`.`attribute_id` = `o2`.`attribute_id`
                   AND (`o`.`sort_order` >= `o2`.`sort_order` AND `o`.`sort_order` < 4
                          OR `o`.`sort_order` = `o2`.`sort_order` AND `o`.`sort_order` = 4)
              CROSS JOIN (SELECT (@option_id := 0)) AS x
            WHERE (`o`.`attribute_id` = ' . $attributeId . ')
            ORDER BY `o`.`sort_order` ASC) `g`
          GROUP BY `g`.`sort_order`';

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

    protected function _getIfExprByGroupedValues($fieldExpr, $values) {
        foreach ($values as $source => $target) {

            $sort_order = $target["sort_order"];
            $option_id = $target["option_id"];
            if (empty($result)) {
                $result = "'$option_id'";
            }
            $result = "IF($fieldExpr = $sort_order, '$option_id', $result)";
        }
        return $result;
    }

    protected function _getVisibilityAttribute() {
        return $this->_getAttributeByCode ('visibility');
    }
}