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
class ManaPro_FilterAttributes_Resource_StockAvailability extends ManaPro_FilterAttributes_Resource_Type   {
    public function process($indexer, $options){
        $attributeCode = $this->getAttributeCode();
        $db = $this->_getWriteAdapter();

        /* @var $res Mage_Core_Model_Resource */
        $res = Mage::getSingleton('core/resource');

        $t = Mage::helper("manapro_filterattributes");
        $attribute = $this->_getAttributeByCode($attributeCode);
        $attributeTable = $attribute['backend_table']
            ? $attribute['backend_table']
            : 'catalog_product_entity_' . $attribute['backend_type'];
        $values = $this->_getAttributeValues($attribute['attribute_id']);
        $v = "IF(`s`.`qty` > 0, {$values[1]}, {$values[0]})";

        $db->beginTransaction();

        try {
            // DELETE stock status values
            if (isset($options['product_id'])) {
                 $deleteCondition = array(
                     'attribute_id = ?' => new Zend_Db_Expr($attribute['attribute_id']),
                     'store_id  = ?' => new Zend_Db_Expr("0"),
                     'entity_id = ?' => new Zend_Db_Expr($options['product_id'])
                 );
            }
            else {
                $deleteCondition = array(
                    'attribute_id = ?' => new Zend_Db_Expr($attribute['attribute_id']),
                    'store_id  = ?' => new Zend_Db_Expr("0")
                );
            }
            $db->delete(
                $res->getTableName($attributeTable),
                $deleteCondition
                   );

            // INSERT all stock status values
            $fields = array(
                'entity_type_id' => new Zend_Db_Expr("`e`.`entity_type_id`"),
                'attribute_id' => new Zend_Db_Expr($attribute['attribute_id']),
                'store_id' => new Zend_Db_Expr("0"),
                'entity_id' => new Zend_Db_Expr("`e`.`entity_id`"),
                'value' => new Zend_Db_Expr($v),
            );

            /* @var $select Varien_Db_Select */
            $select = $db->select()
                ->from(array('e' => $this->getTable('catalog/product')), null)
                 ->joinInner(array('s' =>  $this->getTable('cataloginventory/stock_item')),
                    "`e`.`entity_id` = `s`.`product_id` AND `s`.`is_in_stock` = 1", null)
                ->columns($fields);

            if (isset($options['product_id'])) {
                $select->where("`e`.`entity_id` = ?", $options['product_id']);
            }

            // convert SELECT into UPDATE which acts as INSERT on DUPLICATE unique keys
            $sql = $select->insertFromSelect($res->getTableName($attributeTable), array_keys($fields));

            // run the statement
            $db->query($sql);

            $db->commit();

            if (isset($options['product_id'])) {
                $this->getIndexer()->processEntityAction( new Varien_Object(array(
                    'attributes_data' => array($attributeCode => $attributeCode),
                    'product_ids' => array($options['product_id']),
                )), Mage_Catalog_Model_Product::ENTITY, Mage_Index_Model_Event::TYPE_MASS_ACTION );
            }

        }
        catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

    }

    public function getAttributeCode ( ) {
        return  "stock_availability";
    }

    protected function _getAttributeValues ( $attributeId) {
        $inStockOptionPosition = Mage::getStoreConfig('mana_filters/general/instock_option_position');
        $db = $this->_getReadAdapter();

        $select = $db->select()
            ->from(array('o' => $this->getTable('eav/attribute_option')), array('sort_order', 'option_id'))
            ->where("`o`.`attribute_id` = ?", $attributeId)
            ->order('sort_order');

        $v = $db->fetchAll($select);
        $values = array();
        foreach ($v as $value)
            {if ($value['sort_order'] == $inStockOptionPosition)
                {
                    $values[1] = $value['option_id'];
                } else
                {
                    $values[0] = $value['option_id'];
                }
            }
        return  $values;
    }

    /**
     * @return Mage_Index_Model_Indexer
     */
    public function getIndexer() {
        return Mage::getSingleton('index/indexer');
    }
}