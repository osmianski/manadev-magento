<?php
/** 
 * @category    Mana
 * @package     Mana_Seo
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Seo_Resource_Item extends Mana_Filters_Resource_ItemAdditionalInfo {
    /**
     * @param Varien_Db_Select $select
     * @param Mana_Filters_Model_Filter_Attribute $filter
     * @return mixed
     */
    public function selectItems($select, $filter) {
        // TODO: Implement selectItems() method.
    }

    /**
     * @param Varien_Db_Select $select
     * @param Mana_Filters_Model_Filter_Attribute $filter
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @return mixed
     */
    public function countItems($select, $filter, $collection) {
        $db = $this->_getReadAdapter();
        $schema = $this->seoHelper()->getActiveSchema(Mage::app()->getStore()->getId());

        $fields = array(
            'seo_include_filter_name' => new Zend_Db_Expr("`url`.`final_include_filter_name`"),
            'seo_position' => new Zend_Db_Expr("`url`.`position`"),
            'seo_id' => new Zend_Db_Expr("`url`.`id`"),
            'seo_url_key' => new Zend_Db_Expr("`url`.`final_url_key`"),
        );
        $select
            ->joinLeft(array('url' => $this->getTable('mana_seo/url')),
                $db->quoteInto("`url`.`option_id` = `eav`.`value` AND `url`.`status` = 'active' AND `url`.`schema_id` = ?", $schema->getId()),
                null)
            ->columns($fields)
            ->group($fields);
    }

    #region Dependencies

    /**
     * @return Mana_Seo_Helper_Data
     */
    public function seoHelper() {
        return Mage::helper('mana_seo');
    }
    #endregion

}