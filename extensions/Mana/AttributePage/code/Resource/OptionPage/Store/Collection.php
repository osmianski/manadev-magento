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
                $db->quoteInto("`op_g`.`attribute_page_global_id` = ?", $attributePageGlobalId), null);
        return $this;
    }

    /**
     * @param $storeId
     * @param $productId
     * @return $this
     */
    public function addProductFilter($storeId, $productId) {
/** SELECT *
  FROM m_option_page_store main_table
       INNER JOIN m_option_page_global op_g
          ON op_g.id = main_table.option_page_global_id
       INNER JOIN m_attribute_page_global_custom_settings ap_gcs
          ON op_g.attribute_page_global_id = ap_gcs.id
       INNER JOIN catalog_product_index_eav i
          ON     ap_gcs.attribute_id_0 = i.attribute_id
             AND op_g.option_id_0 = i.value
             AND main_table.store_id = i.store_id
 WHERE main_table.store_id = 1 AND i.entity_id = 16
 */
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
}