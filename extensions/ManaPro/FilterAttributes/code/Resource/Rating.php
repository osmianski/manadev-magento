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
class ManaPro_FilterAttributes_Resource_Rating  extends ManaPro_FilterAttributes_Resource_Type {
    public function process($indexer, $options){
        $attributeCode = $this-> getRatingAttributeCode();
        $db = $this->_getWriteAdapter();

        /* @var $res Mage_Core_Model_Resource */
        $res = Mage::getSingleton('core/resource');

        $t = Mage::helper("manapro_filterattributes");
        $attribute = $this->_getAttributeByCode($attributeCode);
        $visibilityAttribute = $this->_getVisibilityAttribute();
        $attributeTable = $attribute['backend_table']
            ? $attribute['backend_table']
            : 'catalog_product_entity_' . $attribute['backend_type'];
        $visibilityAttributeTable = $visibilityAttribute['backend_table']
                ? $visibilityAttribute['backend_table']
                : 'catalog_product_entity_' . $visibilityAttribute['backend_type'];

        $values = $this->_getAttributeValues( $attribute['attribute_id']);
        $v = $this->_getIfExprByGroupedValues("`ss`.`average_rating`", $values);

        $db->beginTransaction();

        try {
            // DELETE stock status values
            if (isset($options['product_id'])) {
                $deleteCondition = array(
                    'attribute_id = ?' => new Zend_Db_Expr($attribute['attribute_id']),
                    'store_id  = ?' => new Zend_Db_Expr("0"),
                    'entity_id = ?' => new Zend_Db_Expr($options['product_id'])
                );
            } else {
                $deleteCondition = array(
                    'attribute_id = ?' => new Zend_Db_Expr($attribute['attribute_id']),
                    'store_id  = ?' => new Zend_Db_Expr("0")
                );
            }
            $db->delete(
                $attributeTable,
                $deleteCondition
            );

            // INSERT all rating value for default store
            $fields = array(
                'entity_type_id' => new Zend_Db_Expr("`e`.`entity_type_id`"),
                'attribute_id' => new Zend_Db_Expr($attribute['attribute_id']),
                'store_id' => new Zend_Db_Expr("0"),
                'entity_id' => new Zend_Db_Expr("`e`.`entity_id`"),
                'value' => new Zend_Db_Expr($v),
            );
            $subSelect = $db->select()
                ->from(array('r' => $this->getTable('review/review')), null)
                ->joinInner(array('v' => $this->getTable('rating/rating_option_vote')), "`r`.`review_id` = `v`.`review_id`", null)
                ->joinInner(array('o' => $this->getTable('rating/rating_option')), "`v`.`option_id` = `o`.`option_id`", null)
                ->where("`r`.`status_id` = ?",Mage_Review_Model_Review::STATUS_APPROVED )
                ->group('r.entity_pk_value')
                ->columns(array('product_id' => 'r.entity_pk_value',
                                'store_id' => '0',
                                'average_rating' => 'floor(avg(o.value))'));

            /* @var $select Varien_Db_Select */
            $select = $db->select()
                ->from(array('e' => $this->getTable('catalog/product')), null)
                ->joinLeft(array('ss' =>  new Zend_Db_Expr('('.$subSelect.')')), "`e`.`entity_id` = `ss`.`product_id`", null)
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

            // INSERT all rating value for all stores
            $fields = array(
                'entity_type_id' => new Zend_Db_Expr("`e`.`entity_type_id`"),
                'attribute_id' => new Zend_Db_Expr($attribute['attribute_id']),
                'store_id' => new Zend_Db_Expr("`ss`.`store_id`"),
                'entity_id' => new Zend_Db_Expr("`e`.`entity_id`"),
                'value' => new Zend_Db_Expr($v),
            );
            $subSelect = $db->select()
                ->from(array('r' => $this->getTable('review/review')), null)
                ->joinInner(array('s' => $this->getTable('review/review_store')), "`r`.`review_id` = `s`.`review_id`", null)
                ->joinInner(array('v' => $this->getTable('rating/rating_option_vote')), "`r`.`review_id` = `v`.`review_id`", null)
                ->joinInner(array('o' => $this->getTable('rating/rating_option')), "`v`.`option_id` = `o`.`option_id`", null)
                ->where("`r`.`status_id` = ?",Mage_Review_Model_Review::STATUS_APPROVED )
                ->group('r.entity_pk_value')
                ->group('s.store_id')
                ->columns(array('product_id' => 'r.entity_pk_value',
                                'store_id' => 's.store_id',
                                'average_rating' => 'floor(avg(o.value))'));

            /* @var $select Varien_Db_Select */
            $select = $db->select()
                ->from(array('e' => $this->getTable('catalog/product')), null)
                ->joinInner(array('ss' =>  new Zend_Db_Expr('('.$subSelect.')')), "`e`.`entity_id` = `ss`.`product_id`", null)
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


    public function getRatingAttributeCode ( ) {
        return  "rating";
    }

    public function getOptionName( $optionValue ) {
        if ($optionValue > 0) {
            return $optionValue . Mage::helper("manapro_filterattributes")->__(" & up");
        }
        else {
            return Mage::helper("manapro_filterattributes")-> __("non rated");
        }
    }

    public function _getMinRatingValue ( ) {
        return  1;
    }
    public function _getMaxRatingValue ( ) {
        return  5;
    }

    /**
     * @return Mage_Index_Model_Indexer
     */
    public function getIndexer()
    {
        return Mage::getSingleton('index/indexer');
    }
}