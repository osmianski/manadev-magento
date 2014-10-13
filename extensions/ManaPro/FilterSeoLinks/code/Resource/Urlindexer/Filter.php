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
class ManaPro_FilterSeoLinks_Resource_UrlIndexer_Filter extends Mana_Seo_Resource_AttributeUrlIndexer {
    /**
     * @param Mana_Seo_Model_UrlIndexer $indexer
     * @param Mana_Seo_Model_Schema $schema
     * @param array $options
     */
    public function process($indexer, $schema, $options) {
        if (!isset($options['attribute_id']) && !isset($options['store_id']) &&
            !isset($options['schema_global_id']) && !isset($options['schema_store_id']) && !$options['reindex_all']
        ) {
            return;
        }
        $db = $this->_getWriteAdapter();

        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper('mana_core');

        $urlKeyExpr = $schema->getUseFilterLabels()
            ? ($core->isManadevLayeredNavigationInstalled()
                ? $this->_seoify('`f`.`name`', $schema)
                : $this->_seoify('COALESCE(`l`.`value`, `a`.`frontend_label`)', $schema)
            )
            : "REPLACE(LOWER(`a`.`attribute_code`), '_', '-')";
        $fields = array(
            'url_key' => new Zend_Db_Expr($urlKeyExpr),
            'internal_name' => new Zend_Db_Expr('`a`.`attribute_code`'),
            'position' => new Zend_Db_Expr(
                $core->isManadevSeoLayeredNavigationInstalled() ? '`f`.`url_position`' :
                ($core->isManadevLayeredNavigationInstalled() ? '`f`.`position`':
                '`ca`.`position`')),
            'filter_display' => new Zend_Db_Expr($core->isManadevLayeredNavigationInstalled() ? '`f`.`display`' : 'NULL'),
            'type' => new Zend_Db_Expr("IF(a.backend_type = 'decimal', ".
                "'" . Mana_Seo_Model_ParsedUrl::PARAMETER_PRICE . "', ".
                "'" . Mana_Seo_Model_ParsedUrl::PARAMETER_ATTRIBUTE ."')"),
            'is_page' => new Zend_Db_Expr('0'),
            'is_parameter' => new Zend_Db_Expr('1'),
            'is_attribute_value' => new Zend_Db_Expr('0'),
            'is_category_value' => new Zend_Db_Expr('0'),
            'schema_id' => new Zend_Db_Expr($schema->getId()),
            'attribute_id' => new Zend_Db_Expr('`a`.`attribute_id`'),
            'unique_key' => new Zend_Db_Expr("CONCAT(`a`.`attribute_id`, '-', $urlKeyExpr)"),
            'status' => new Zend_Db_Expr("'". Mana_Seo_Model_Url::STATUS_ACTIVE ."'"),
            'description' => new Zend_Db_Expr(
                "CONCAT('{$this->seoHelper()->__('Attribute')} \\'', ".
                "COALESCE(`l`.`value`, `a`.`frontend_label`), '\\' ({$this->seoHelper()->__('code')} \\'', ".
                "`a`.`attribute_code`, '\\'). ', IF(a.backend_type = 'decimal', '{$this->seoHelper()->__('Always Added before decimal value range')}', '{$this->seoHelper()->__('Added before filter/attribute value(s)/option(s) if showing filter name in URL is enabled')}'))"),
        );

        /* @var $select Varien_Db_Select */
        $select = $this->_getFilterableAttributeSelect($db)
            ->joinLeft(array('l' => $this->getTable('eav/attribute_label')),
                $db->quoteInto("`l`.`attribute_id` = `a`.`attribute_id` AND `l`.`store_id` = ?", $schema->getStoreId()),
                null);

        if ($core->isManadevLayeredNavigationInstalled()) {
            $select
                ->joinInner(array('g' => $this->getTable('mana_filters/filter2')), '`g`.`code` = `a`.`attribute_code`', null)
                ->joinInner(array('f' => $this->getTable('mana_filters/filter2_store')),
                    $db->quoteInto("`f`.`global_id` = `g`.`id` AND `f`.`store_id` = ?", $schema->getStoreId()),
                    null);
        }

        $obsoleteCondition = "(`schema_id` = " . $schema->getId() . ") AND (`is_parameter` = 1) AND ".
            "(`type` IN ('". Mana_Seo_Model_ParsedUrl::PARAMETER_PRICE ."', '" .
            Mana_Seo_Model_ParsedUrl::PARAMETER_ATTRIBUTE ."'))";
        if (isset($options['attribute_id'])) {
            $select->where('`a`.`attribute_id` = ?', $options['attribute_id']);
            $obsoleteCondition .= ' AND (`attribute_id` = ' . $options['attribute_id'] . ')';
        }

        $select->columns($fields);

        // convert SELECT into UPDATE which acts as INSERT on DUPLICATE unique keys
        $this->logger()->logUrlIndexer('-----------------------------');
        $this->logger()->logUrlIndexer(get_class($this));
        $this->logger()->logUrlIndexer($select->__toString());
        $this->logger()->logUrlIndexer($schema->getId());
        $this->logger()->logUrlIndexer($obsoleteCondition);
        $this->logger()->logUrlIndexer(json_encode($options));
        $selectSql = $select->__toString();
        $sql = $select->insertFromSelect($this->getTargetTableName(), array_keys($fields));

        // run the statement
        $this->makeAllRowsObsolete($options, $obsoleteCondition);
        $db->exec($sql);
    }
}