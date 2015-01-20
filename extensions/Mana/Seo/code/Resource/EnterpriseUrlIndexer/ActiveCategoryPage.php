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
class Mana_Seo_Resource_EnterpriseUrlIndexer_ActiveCategoryPage extends Mana_Seo_Resource_CategoryUrlIndexer {
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

        /* @var $db Varien_Db_Adapter_Pdo_Mysql */
        $db = $this->_getWriteAdapter();

        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper('mana_core');

        if ($core->isEnterpriseUrlRewriteInstalled()) {
            $categoryIdExpr = "SUBSTRING(`r`.`target_path`, CHAR_LENGTH('catalog/category/view/id/') + 1)";
            $fields = array(
                'url_key' => new Zend_Db_Expr('`r`.`request_path`'),
                'type' => new Zend_Db_Expr("'category'"),
                'is_page' => new Zend_Db_Expr('1'),
                'is_parameter' => new Zend_Db_Expr('0'),
                'is_attribute_value' => new Zend_Db_Expr('0'),
                'is_category_value' => new Zend_Db_Expr('0'),
                'schema_id' => new Zend_Db_Expr($schema->getId()),
                'category_id' => new Zend_Db_Expr("CAST($categoryIdExpr AS unsigned)"),
                'unique_key' => new Zend_Db_Expr("CONCAT($categoryIdExpr, '-', `r`.`is_system`)"),
                'status' => new Zend_Db_Expr("'" . Mana_Seo_Model_Url::STATUS_ACTIVE . "'"),
                'description' => new Zend_Db_Expr(
                    "CONCAT('{$this->seoHelper()->__('Category')} ID ', ".
                    "CAST($categoryIdExpr AS unsigned), ' {$this->seoHelper()->__('page')}')"),
            );

            /* @var $select Varien_Db_Select */
            $select = $db->select()
                ->from(array('r' => $this->getTable('enterprise_urlrewrite/url_rewrite')), null)
                ->joinLeft(array('rel' => $this->getTable('enterprise_urlrewrite/redirect_rewrite')), "rel.url_rewrite_id = r.url_rewrite_id", null)
                ->join(array('c' => $this->getTable('catalog/category')), "c.entity_id = " . $fields['category_id'], null)
                ->columns($fields)
                ->where("`r`.`target_path` LIKE 'catalog/category/view/id/%'")
                ->where('rel.url_rewrite_id IS NULL')
                ->where("`r`.`store_id` = ?", $schema->getStoreId());

            if (isset($options['category_id'])) {
                $categoryIds = $this->_getChildCategoryIds($options['category_id'], $options['category_path']);
                $paths = array();
                foreach ($categoryIds as $categoryId) {
                    $paths[] = 'catalog/category/view/id/'.$categoryId;
                }
                $select->where('`r`.`target_path` IN (?)', $paths);
            }

            // convert SELECT into UPDATE which acts as INSERT on DUPLICATE unique keys
            $this->logger()->logUrlIndexer('-----------------------------');
            $this->logger()->logUrlIndexer(get_class($this));
            $this->logger()->logUrlIndexer($select->__toString());
            $this->logger()->logUrlIndexer($schema->getId());
            $this->logger()->logUrlIndexer(json_encode($options));
            $sql = $select->insertFromSelect($this->getTargetTableName(), array_keys($fields));

            // run the statement
            $db->exec($sql);
        }
    }
}