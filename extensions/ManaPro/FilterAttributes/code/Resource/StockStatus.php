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
class ManaPro_FilterAttributes_Resource_StockStatus extends ManaPro_FilterAttributes_Resource_Type   {
    public function process($indexer, $options){
        $attributeCode = $this-> getStockStatusAttributeCode();
        $db = $this->_getWriteAdapter();

        /* @var $res Mage_Core_Model_Resource */
        $res = Mage::getSingleton('core/resource');

        $t = Mage::helper("manapro_filterattributes");
        $attribute = $this->_getAttributeByCode($attributeCode);
        $visibilityAttribute = $this->_getAttributeByCode('visibility');
        $attributeTable = $attribute['backend_table']
            ? $attribute['backend_table']
            : 'catalog_product_entity_' . $attribute['backend_type'];
        $visibilityAttributeTable = $visibilityAttribute['backend_table']
                ? $visibilityAttribute['backend_table']
                : 'catalog_product_entity_' . $visibilityAttribute['backend_type'];
        $values = $this->_getStockStatusAttributeValues($attribute['attribute_id']);
        //IF(`s`.`is_in_stock` = 0, 128, 129)
        $v = $this->_getIfExpr("`s`.`is_in_stock`", $values);

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
                $attributeTable,
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
                 ("`e`.`entity_id` = `s`.`product_id`"), null)
                 ->joinInner(array('v' => $visibilityAttributeTable),
                 $db->quoteInto("`e`.`entity_id` = `v`.`entity_id` ".
                 " AND `v`.`store_id` = 0 ".
                 " AND `v`.`value` <> 1".
                 " AND `v`.`attribute_id` = ?", $visibilityAttribute['attribute_id']), null)
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

    public function getStockStatusAttributeCode ( ) {
        return  "stock_status";
    }

    protected function _getStockStatusAttributeValues ( $attributeId) {
        $inStockOptionPosition = Mage::getStoreConfig('mana_filters/general/instock_option_position');

/*    	$v = $this->_getReadAdapter()->fetchAll("
            SELECT `o`.`sort_order`, `o`.`option_id`
              FROM `eav_attribute_option` `o`
             WHERE `o`.`attribute_id` = $attributeId
            ORDER BY `o`.`sort_order`");
*/
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

    protected function _addAttributeAllSets ( $attributeId ) {

        $model=Mage::getModel('eav/entity_setup','core_setup');
        $allAttributeSetIds=$model->getAllAttributeSetIds('catalog_product');
        foreach ($allAttributeSetIds as $attributeSetId) {
            try{
                $attributeGroupId=$model->getAttributeGroup('catalog_product',$attributeSetId,'General');
            }
            catch(Exception $e) {
                $attributeGroupId=$model->getDefaultArrtibuteGroupId('catalog/product',$attributeSetId);
            }
            $model->addAttributeToSet('catalog_product',$attributeSetId,$attributeGroupId, $attributeId);
        }
        return  "stock_status";
    }

    /**
     * @return Mage_Index_Model_Indexer
     */
    public function getIndexer() {
        return Mage::getSingleton('index/indexer');
    }
}