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
class Mana_Seo_Resource_UrlIndexer_CategoryPage extends Mana_Seo_Resource_CategoryUrlIndexer {
    /**
     * @param Mana_Seo_Model_UrlIndexer $indexer
     * @param Mana_Seo_Model_Schema $schema
     * @param array $options
     */
    public function process($indexer, $schema, $options) {
        if (!isset($options['category_id']) && !isset($options['store_id']) &&
            !isset($options['schema_global_id']) && !isset($options['schema_store_id']) && !$options['reindex_all'])
        {
            return;
        }

        $db = $this->_getWriteAdapter();

        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper('mana_core');

        /* @var $mbstring Mana_Core_Helper_Mbstring */
        $mbstring = Mage::helper('mana_core/mbstring');

        /* @var $categoryHelper Mage_Catalog_Helper_Category */
        $categoryHelper = Mage::helper('catalog/category');

        $suffix = $core->addDotToSuffix($categoryHelper->getCategoryUrlSuffix($schema->getStoreId()));

        $nameAttribute = $core->getAttribute('catalog_category', 'name', array('attribute_id', 'backend_type', 'backend_table'));
        $nameAttributeTable = $core->getAttributeTable($nameAttribute);

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
            'description' => new Zend_Db_Expr(
                "CONCAT('{$this->seoHelper()->__('Category')} \\'', ".
                "COALESCE(`ns`.`value`, `ng`.`value`), '\\' (ID ', `r`.`category_id`, ') {$this->seoHelper()->__('page')}')"),
        );

        /* @var $select Varien_Db_Select */
        $select = $db->select()
            ->from(array('r' => $this->getTable('core/url_rewrite')), null)
            ->joinInner(array('cat' => $this->getTable('catalog/category')), "cat.entity_id = `r`.`category_id`", null)
            ->joinLeft(array('ng' => $nameAttributeTable),
                "`ng`.`entity_id` = `r`.`category_id`" .
                $db->quoteInto(" AND `ng`.`attribute_id` = ?", $nameAttribute['attribute_id']) .
                " AND `ng`.`store_id` = 0", null)
            ->joinLeft(array('ns' => $nameAttributeTable),
                "`ns`.`entity_id` = `r`.`category_id`" .
                $db->quoteInto(" AND `ns`.`attribute_id` = ?", $nameAttribute['attribute_id']) .
                $db->quoteInto(" AND `ns`.`store_id` = ?", $schema->getStoreId()), null)
            ->columns($fields)
            ->where('`r`.`category_id` IS NOT NULL')
            ->where('`r`.`store_id` = ?', $schema->getStoreId())
            ->where('`r`.`product_id` IS NULL');

        $obsoleteCondition = "(`schema_id` = ". $schema->getId() .") AND (`is_page` = 1) AND (`type` = 'category')";
        if (isset($options['category_id'])) {
            $categoryIds = $this->_getChildCategoryIds($options['category_id'], $options['category_path']);
            $select->where('`r`.`category_id` IN (?)', $categoryIds);
            $obsoleteCondition .= ' AND (`category_id` IN (' . implode(',', $categoryIds) .'))';
        }

        // convert SELECT into UPDATE which acts as INSERT on DUPLICATE unique keys
        $this->logger()->logUrlIndexer('-----------------------------');
        $this->logger()->logUrlIndexer(get_class($this));
        $this->logger()->logUrlIndexer($select->__toString());
        $this->logger()->logUrlIndexer($schema->getId());
        $this->logger()->logUrlIndexer($obsoleteCondition);
        $this->logger()->logUrlIndexer(json_encode($options));
        $sql = $select->insertFromSelect($this->getTargetTableName(), array_keys($fields));

        // run the statement
        $this->makeAllRowsObsolete($options, $obsoleteCondition);
        if (!$core->isEnterpriseUrlRewriteInstalled()) {
            $db->exec($sql);
        }
    }
}