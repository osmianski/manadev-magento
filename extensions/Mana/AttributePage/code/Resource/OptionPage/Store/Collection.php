<?php
/** 
 * @category    Mana
 * @package     Mana_AttributePage
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_AttributePage_Resource_OptionPage_Store_Collection extends Mana_AttributePage_Resource_OptionPage_Abstract_Collection {
    protected function _construct() {
        $this->_init(Mana_AttributePage_Model_OptionPage_Store::ENTITY);
    }


    /**
     * @param $attributePageGlobalId
     * @return $this
     */
    public function addAttributePageFilter($attributePageGlobalId) {
        $db = $this->getConnection();
        $this->getSelect()
            ->joinInner(array('op_g' => $this->getTable('mana_attributepage/optionPage_global')),
                "`op_g`.`id` = `main_table`.`option_page_global_id` AND ".
                $db->quoteInto("`op_g`.`attribute_page_global_id` = ?", $attributePageGlobalId),
                array('option_id_0', 'option_id_1', 'option_id_2', 'option_id_3', 'option_id_4'));
        return $this;
    }

    public function addHavingProductsFilter() {
        $db = $this->getConnection();
        if ($sampleOptionPage = $this->getSampleItem()) {
            // basic SQL selecting all products
            $productSelect = $db->select()
                ->from(array('e' => $this->getTable('catalog/product')), 'entity_id');

            // get number of attributes which option based are based upon
            if (!$sampleOptionPage->getData('option_id_1')) {
                $count = 1;
            }
            elseif (!$sampleOptionPage->getData('option_id_2')) {
                $count = 2;
            }
            elseif (!$sampleOptionPage->getData('option_id_3')) {
                $count = 3;
            }
            elseif (!$sampleOptionPage->getData('option_id_4')) {
                $count = 4;
            }
            else {
                $count = 5;
            }

            // add a condition for each option id
            for ($i = 0; $i < $count; $i++) {
                $productSelect
                    ->joinInner(array("eav_$i" => $this->getTable('catalog/product_index_eav')),
                        $db->quoteInto("`e`.`entity_id` = `eav_$i`.`entity_id` AND
                            `eav_$i`.`store_id` = ?", Mage::app()->getStore()->getId()),
                        null)
                    ->where("`eav_$i`.`value` = `op_g`.`option_id_$i`");
            }
            $this->getSelect()->where("EXISTS($productSelect)");
        }
        return $this;
    }

    protected function _getAlphaExpr() {
        return new Zend_Db_Expr("CASE WHEN main_table.title REGEXP '^[0-9]' THEN '#' ELSE LEFT(upper(main_table.title), 1) END");
    }
    public function addAlphaFilter($alpha) {
        $this->getSelect()->where("({$this->_getAlphaExpr()}) = upper(?)", $alpha);
        return $this;
    }

    public function addAlphaColumn() {
        $this->getSelect()->columns(array('alpha' => $this->_getAlphaExpr()));

        return $this;
    }

//    public function addProductCount() {
//        $db = $this->getConnection();
//        $productSelect = $db->select()
//            ->from(array("p" => $this->getTable('catalog/product')),
//                array('count' => new Zend_Db_Expr("COUNT(DISTINCT `p`.`entity_id`)")));
//        for ($i = 0; $i < Mana_AttributePage_Model_AttributePage_Abstract::MAX_ATTRIBUTE_COUNT; $i++) {
//            $productSelect = $db->select()
//                ->joinLeft(array("eav_$i" => $this->getTable('catalog/product_index_eav')),
//                    "`eav_$i`.`entity_id` = `p`.`entity_id`", null)
//                ->where("`eav_$i`.`value` = `op_g`.`option_id_$i`");
//        }
//        $this->getSelect()->columns(array(
//            'product_count' => new Zend_Db_Expr("($productSelect)"),
//        ));
//        return $this;
//    }

    /**
     * @param $storeId
     * @param $productId
     * @return $this
     */
    public function addProductFilter($storeId, $productId) {
       $this->getSelect()
            ->joinInner(array('op_g' => $this->getTable('mana_attributepage/optionPage_global')),
                "`op_g`.`id` = `main_table`.`option_page_global_id`", null)
            ->joinInner(array('ap_gcs' => $this->getTable('mana_attributepage/attributePage_globalCustomSettings')),
                "`op_g`.`attribute_page_global_id` = `ap_gcs`.`id`", null)
            ->joinInner(array('i' => $this->getTable('catalog/product_index_eav')),
                "`ap_gcs`.`attribute_id_0` = `i`.`attribute_id` AND ".
                "`op_g`.`option_id_0` = `i`.`value` AND ".
                "`main_table`.`store_id` = `i`.`store_id` ", null)
            ->where("`main_table`.`store_id` = ?", $storeId)
            ->where("`i`.`entity_id` = ?", $productId);

    return $this;
    }

    /**
     * @param $storeId
     * @return $this
     */
    public function addStoreFilter($storeId) {
        $this->getSelect()
            ->where("`main_table`.`store_id` = ?", $storeId);
        return $this;
    }

    /**
     * @return $this
     */
    public function addFeaturedFilter() {
        $db = $this->getConnection();
        $this->getSelect()
                ->where("`main_table`.`is_featured` = ?", 1);
        return $this;
    }

    public function getSampleItem() {
        $db = $this->getConnection();
        $select = clone $this->getSelect();
        if ($data = $db->fetchRow($select->limit(1))) {
            return $this->getNewEmptyItem()->addData($data);
        }
        else {
            return false;
        }
    }
}