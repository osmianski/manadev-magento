<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterSeoLinks
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Seo_Resource_UrlIndexer_Filter extends Mana_Seo_Resource_UrlIndexer {
    /**
     * @param Mana_Seo_Model_UrlIndexer $indexer
     * @param array $options
     */
    public function process($indexer, $options) {
        $db = $this->_getWriteAdapter();

        /* @var $urlHelper Mana_Seo_Helper_Url_Filter */
        $urlHelper = Mage::helper('mana_seo/url_filter');

        foreach ($this->_getSchemas() as $schema) {
            $urlKeyExpr = $schema->getUseFilterLabels()
                ? ($urlHelper->isManadevLayeredNavigationInstalled()
                    ? $this->_seoify('`f`.`name`', $schema)
                    : $this->_seoify('COALESCE(`l`.`value`, `a`.`frontend_label`)', $schema)
                )
                : "REPLACE(LOWER(`g`.`code`), '_', '-')";
            $fields = array(
                'url_key' => new Zend_Db_Expr($urlKeyExpr),
                'type' => new Zend_Db_Expr("'mana_seo/url_filter'"),
                'schema_id' => new Zend_Db_Expr($schema->getId()),
                'store_id' => new Zend_Db_Expr($schema->getStoreId()),
                'attribute_id' => new Zend_Db_Expr('`a`.`attribute_id`'),
                'unique_key' => new Zend_Db_Expr("CONCAT('{$schema->getId()}-', `a`.`attribute_id`, '-', $urlKeyExpr)"),
                'status' => new Zend_Db_Expr("'{$schema->getStatus()}'"),
            );

            /* @var $select Varien_Db_Select */
            $select = $this->_getFilterableAttributeSelect($db)
                ->joinLeft(array('l' => $this->getTable('eav/attribute_label')),
                    $db->quoteInto("`l`.`attribute_id` = `a`.`attribute_id` AND `l`.`store_id` = ?", $schema->getStoreId()),
                    null);

            if ($urlHelper->isManadevLayeredNavigationInstalled()) {
                $select
                    ->joinInner(array('g' => $this->getTable('mana_filters/filter2')), '`g`.`code` = `a`.`attribute_code`', null)
                    ->joinInner(array('f' => $this->getTable('mana_filters/filter2_store')),
                        $db->quoteInto("`f`.`global_id` = `g`.`id` AND `f`.`store_id` = ?", $schema->getStoreId()),
                        null);
            }

            $select->columns($fields);

            // convert SELECT into UPDATE which acts as INSERT on DUPLICATE unique keys
            $sql = $select->insertFromSelect($this->getTargetTableName(), array_keys($fields));

            // run the statement
            $db->query($sql);
        }
    }
}