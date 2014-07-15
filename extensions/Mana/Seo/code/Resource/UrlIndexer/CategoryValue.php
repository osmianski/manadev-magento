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
class Mana_Seo_Resource_UrlIndexer_CategoryValue extends Mana_Seo_Resource_CategoryUrlIndexer {
    /**
     * @param Mana_Seo_Model_UrlIndexer $indexer
     * @param Mana_Seo_Model_Schema $schema
     * @param array $options
     */
    public function process($indexer, $schema, $options) {
        if (!isset($options['category_id']) && !isset($options['store_id']) &&
            !isset($options['schema_global_id']) && !isset($options['schema_store_id']) && !$options['reindex_all']
        ) {
            return;
        }

        $db = $this->_getWriteAdapter();

        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper('mana_core');
        $urlKeyAttribute = $core->getAttribute('catalog_category', 'url_key', array('attribute_id', 'backend_type', 'backend_table'));
        $urlKeyAttributeTable = $core->getAttributeTable($urlKeyAttribute);
        $isActiveAttribute = $core->getAttribute('catalog_category', 'is_active', array('attribute_id', 'backend_type', 'backend_table'));
        $isActiveAttributeTable = $core->getAttributeTable($isActiveAttribute);
        $nameAttribute = $core->getAttribute('catalog_category', 'name', array('attribute_id', 'backend_type', 'backend_table'));
        $nameAttributeTable = $core->getAttributeTable($nameAttribute);

        /* @var $rootCategory Mage_Catalog_Model_Category */
        $rootCategory = Mage::getModel('catalog/category');
        $rootCategory
            ->setStoreId($schema->getStoreId())
            ->load(Mage::app()->getStore($schema->getStoreId())->getRootCategoryId());
        /** @noinspection PhpUndefinedMethodInspection */
        $pathPattern = $rootCategory->getPath() . '/%';

        $urlKeyExpr = "COALESCE(`us`.`value`, `ug`.`value`)";
        $fields = array(
            'url_key' => new Zend_Db_Expr($urlKeyExpr),
            'type' => new Zend_Db_Expr("'category'"),
            'is_page' => new Zend_Db_Expr('0'),
            'is_parameter' => new Zend_Db_Expr('0'),
            'is_attribute_value' => new Zend_Db_Expr('0'),
            'is_category_value' => new Zend_Db_Expr('1'),
            'schema_id' => new Zend_Db_Expr($schema->getId()),
            'category_id' => new Zend_Db_Expr('`e`.`entity_id`'),
            'unique_key' => new Zend_Db_Expr("CONCAT(`e`.`entity_id`, '-', $urlKeyExpr)"),
            'status' => new Zend_Db_Expr("IF (COALESCE(`as`.`value`, `ag`.`value`) = 1, ".
                "'" . Mana_Seo_Model_Url::STATUS_ACTIVE . "', ".
                "'" . Mana_Seo_Model_Url::STATUS_DISABLED . "')"),
            'description' => new Zend_Db_Expr(
                "CONCAT('{$this->seoHelper()->__('Filtered by category')} \\'', " .
                "COALESCE(`ns`.`value`, `ng`.`value`), '\\' (ID ', `e`.`entity_id`, ')')"),
        );

        /* @var $select Varien_Db_Select */
        $select = $db->select()
            ->from(array('e' => $this->getTable('catalog/category')), null)
            ->joinLeft(array('ug' => $urlKeyAttributeTable),
                "`ug`.`entity_id` = `e`.`entity_id`".
                $db->quoteInto(" AND `ug`.`attribute_id` = ?", $urlKeyAttribute['attribute_id']).
                " AND `ug`.`store_id` = 0", null)
            ->joinLeft(array('us' => $urlKeyAttributeTable),
                "`us`.`entity_id` = `e`.`entity_id`" .
                $db->quoteInto(" AND `us`.`attribute_id` = ?", $urlKeyAttribute['attribute_id']).
                $db->quoteInto(" AND `us`.`store_id` = ?", $schema->getStoreId()), null)
            ->joinLeft(array('ag' => $isActiveAttributeTable),
                "`ag`.`entity_id` = `e`.`entity_id`" .
                $db->quoteInto(" AND `ag`.`attribute_id` = ?", $isActiveAttribute['attribute_id']) .
                " AND `ag`.`store_id` = 0", null)
            ->joinLeft(array('as' => $isActiveAttributeTable),
                "`as`.`entity_id` = `e`.`entity_id`" .
                $db->quoteInto(" AND `as`.`attribute_id` = ?", $isActiveAttribute['attribute_id']) .
                $db->quoteInto(" AND `as`.`store_id` = ?", $schema->getStoreId()), null)
            ->joinLeft(array('ng' => $nameAttributeTable),
                "`ng`.`entity_id` = `e`.`entity_id`" .
                $db->quoteInto(" AND `ng`.`attribute_id` = ?", $nameAttribute['attribute_id']) .
                " AND `ng`.`store_id` = 0", null)
            ->joinLeft(array('ns' => $nameAttributeTable),
                "`ns`.`entity_id` = `e`.`entity_id`" .
                $db->quoteInto(" AND `ns`.`attribute_id` = ?", $nameAttribute['attribute_id']) .
                $db->quoteInto(" AND `ns`.`store_id` = ?", $schema->getStoreId()), null)
            ->columns($fields)
            ->where('`e`.`path` LIKE ?', $pathPattern);

        $obsoleteCondition = "(`schema_id` = " . $schema->getId() . ") AND (`is_category_value` = 1) AND (`type` = 'category')";
        if (isset($options['category_id'])) {
            $categoryIds = $this->_getChildCategoryIds($options['category_id'], $options['category_path']);
            $select->where('`e`.`entity_id` IN (?)', $categoryIds);
            $obsoleteCondition .= ' AND (`category_id` IN (' . implode(',', $categoryIds) . '))';
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
        $db->exec("SET SQL_BIG_SELECTS=1");
        $db->exec($sql);
    }
}