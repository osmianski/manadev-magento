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
class Mana_Seo_Resource_UrlIndexer_Category extends Mana_Seo_Resource_UrlIndexer {
    /**
     * @param Mana_Seo_Model_UrlIndexer $indexer
     * @param array $options
     */
    public function process($indexer, $options) {
        $db = $this->_getWriteAdapter();

        /* @var $mbstring Mana_Core_Helper_Mbstring */
        $mbstring = Mage::helper('mana_core/mbstring');

        /* @var $categoryHelper Mage_Catalog_Helper_Category */
        $categoryHelper = Mage::helper('catalog/category');

        foreach (Mage::app()->getStores(true) as $store) {
            /* @var $store Mage_Core_Model_Store */
            $suffix = $categoryHelper->getCategoryUrlSuffix($store->getId());

            $fields = array(
                'url_key' => new Zend_Db_Expr('SUBSTRING(`r`.`request_path`, 1, CHAR_LENGTH(`r`.`request_path`) - ' . $mbstring->strlen($suffix) . ')'),
                'type' => new Zend_Db_Expr("'mana_seo/url_category'"),
                'is_page' => new Zend_Db_Expr(1),
                'supports_parameters' => new Zend_Db_Expr(1),
                'is_parameter' => new Zend_Db_Expr(0),
                'is_value' => new Zend_Db_Expr(1),
                'store_id' => new Zend_Db_Expr('`r`.`store_id`'),
                'category_id' => new Zend_Db_Expr('`r`.`category_id`'),
                'unique_key' => new Zend_Db_Expr("CONCAT(`r`.`id_path`, '-', `r`.`is_system`)"),
                'status' => new Zend_Db_Expr("IF(`r`.`options` = '', '" .
                    Mana_Seo_Model_Url::STATUS_ACTIVE . "', '" .
                    Mana_Seo_Model_Url::STATUS_OBSOLETE . "')"),
            );

            /* @var $select Varien_Db_Select */
            $select = $db->select()
                ->from(array('r' => $this->getTable('core/url_rewrite')), null)
                ->columns($fields)
                ->where('`r`.`category_id` IS NOT NULL')
                ->where('`r`.`store_id` = ?', $store->getId())
                ->where('`r`.`product_id` IS NULL');

            // convert SELECT into UPDATE which acts as INSERT on DUPLICATE unique keys
            $sql = $select->insertFromSelect($this->getTargetTableName(), array_keys($fields));

            // run the statement
            $db->query($sql);
        }
    }
}