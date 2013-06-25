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
class Mana_Seo_Resource_UrlIndexer_CategoryPage extends Mana_Seo_Resource_UrlIndexer {
    /**
     * @param Mana_Seo_Model_UrlIndexer $indexer
     * @param Mana_Seo_Model_Schema $schema
     * @param array $options
     */
    public function process($indexer, $schema, $options) {
        $db = $this->_getWriteAdapter();

        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper('mana_core');

        /* @var $mbstring Mana_Core_Helper_Mbstring */
        $mbstring = Mage::helper('mana_core/mbstring');

        /* @var $categoryHelper Mage_Catalog_Helper_Category */
        $categoryHelper = Mage::helper('catalog/category');

        $suffix = $core->addDotToSuffix($categoryHelper->getCategoryUrlSuffix($schema->getStoreId()));

        $fields = array(
            'url_key' => new Zend_Db_Expr('SUBSTRING(`r`.`request_path`, 1, CHAR_LENGTH(`r`.`request_path`) - ' . $mbstring->strlen($suffix) . ')'),
            'type' => new Zend_Db_Expr("'category'"),
            'is_page' => new Zend_Db_Expr('1'),
            'is_parameter' => new Zend_Db_Expr('0'),
            'is_attribute_value' => new Zend_Db_Expr('0'),
            'is_category_value' => new Zend_Db_Expr('0'),
            'schema_id' => new Zend_Db_Expr($schema->getId()),
            'category_id' => new Zend_Db_Expr('`r`.`category_id`'),
            'unique_key' => new Zend_Db_Expr("CONCAT(`r`.`id_path`, '-', `r`.`is_system`)"),
            'status' => new Zend_Db_Expr("IF(`r`.`options` = '' OR `r`.`options` IS NULL, '" .
                Mana_Seo_Model_Url::STATUS_ACTIVE . "', '" .
                Mana_Seo_Model_Url::STATUS_OBSOLETE . "')"),
        );

        /* @var $select Varien_Db_Select */
        $select = $db->select()
            ->from(array('r' => $this->getTable('core/url_rewrite')), null)
            ->columns($fields)
            ->where('`r`.`category_id` IS NOT NULL')
            ->where('`r`.`store_id` = ?', $schema->getStoreId())
            ->where('`r`.`product_id` IS NULL');

        // convert SELECT into UPDATE which acts as INSERT on DUPLICATE unique keys
        $sql = $select->insertFromSelect($this->getTargetTableName(), array_keys($fields));

        // run the statement
        $db->raw_query($sql);
    }
}