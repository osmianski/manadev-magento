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
        $attributeCode = $this-> _getStockStatusAttributeCode();
        $db = $this->_getWriteAdapter();

        /* @var $res Mage_Core_Model_Resource */
        $res = Mage::getSingleton('core/resource');

        $t = Mage::helper("manapro_filterattributes");
        $attribute = $this->_getAttributeByCode($attributeCode);
        $attributeTable = $attribute['backend_table']
            ? $attribute['backend_table']
            : 'catalog_product_entity_' . $attribute['backend_type'];

        $values = $this->_getStockStatusAttributeValues($attributeCode, $attribute['attribute_id']);
        //IF(`s`.`is_in_stock` = 0, 128, 129)
        $v = $this->_getIfExpr("`s`.`is_in_stock`", $values);

        $db->beginTransaction();

        try {
            // Add attribute to all attribute sets
            //$this ->_addAttributeAllSets ($attribute['attribute_id']);

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
                ->columns($fields);

            if (isset($options['product_id'])) {
                $select->where("`e`.`entity_id` = ?", $options['product_id']);
            }

            // convert SELECT into UPDATE which acts as INSERT on DUPLICATE unique keys
            $sql = $select->insertFromSelect($res->getTableName($attributeTable), array_keys($fields));

            // run the statement
            $db->query($sql);

            $db->commit();
        }
        catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

    }

    public function getStockStatusAttributeCode ( ) {
        return  "stock_status";
    }

    protected function _getStockStatusAttributeValues ( $attributeCode, $attributeId) {
        $inStockOptionPosition = Mage::getStoreConfig('mana_filters/general/instock_option_position');

    	$v = $this->_getReadAdapter()->fetchAll("
            SELECT `o`.`sort_order`, `o`.`option_id`
              FROM `eav_attribute_option` `o`
             WHERE `o`.`attribute_id` = $attributeId
            ORDER BY `o`.`sort_order`");

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
        // Out of stock option value
/*        $values[0] = 128;
        // In stock option value
        $values[1] = 129;*/
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
}