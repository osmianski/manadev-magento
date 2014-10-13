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
class ManaPro_FilterSeoLinks_Resource_UrlIndexer_Value extends Mana_Seo_Resource_AttributeUrlIndexer {
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

        $urlKeyExpr = $this->_seoify("COALESCE(vs.value, vg.value)", $schema);
        $fields = array(
            'url_key' => new Zend_Db_Expr($urlKeyExpr),
            'internal_name' => new Zend_Db_Expr('`a`.`attribute_code`'),
            'position' => new Zend_Db_Expr('`o`.`sort_order`'),
            'attribute_id' => new Zend_Db_Expr('`o`.`attribute_id`'),
            'type' => new Zend_Db_Expr("'option'"),
            'is_page' => new Zend_Db_Expr('0'),
            'is_parameter' => new Zend_Db_Expr('0'),
            'is_attribute_value' => new Zend_Db_Expr('1'),
            'is_category_value' => new Zend_Db_Expr('0'),
            'include_filter_name' => new Zend_Db_Expr($core->isManadevSeoLayeredNavigationInstalled()
                ? "IF(`f`.include_in_url = '". Mana_Seo_Model_Source_IncludeInUrl::ALWAYS."', 1, ".
                    "IF(`f`.include_in_url = '" . Mana_Seo_Model_Source_IncludeInUrl::NEVER . "', 0, ".
                    "{$schema->getIncludeFilterName()}))"
                : $schema->getIncludeFilterName()),
            'schema_id' => new Zend_Db_Expr($schema->getId()),
            'option_id' => new Zend_Db_Expr('`o`.`option_id`'),
            'unique_key' => new Zend_Db_Expr("CONCAT(`o`.`option_id`, '-', $urlKeyExpr)"),
            'status' => new Zend_Db_Expr("'" . Mana_Seo_Model_Url::STATUS_ACTIVE . "'"),
            'description' => new Zend_Db_Expr(
                "CONCAT('{$this->seoHelper()->__('Filtered by attribute')} \\'', " .
                "COALESCE(`l`.`value`, `a`.`frontend_label`), '\\' ({$this->seoHelper()->__('code')} \\'', " .
                "`a`.`attribute_code`, '\\') {$this->seoHelper()->__('value')} \\'', " .
                "COALESCE(vs.value, vg.value), '\\' (ID ', `o`.`option_id`, ')')"),
        );

        /* @var $select Varien_Db_Select */
        $select = $this->_getFilterableAttributeSelect($db)
            ->joinInner(array('o' => $this->getTable('eav/attribute_option')), "`o`.`attribute_id` = `a`.`attribute_id`", null)
            ->joinLeft(array('vg' => $this->getTable('eav/attribute_option_value')), 'o.option_id = vg.option_id AND vg.store_id = 0', null)
            ->joinLeft(array('vs' => $this->getTable('eav/attribute_option_value')),
                $db->quoteInto('o.option_id = vs.option_id AND vs.store_id = ?', $schema->getStoreId()),
                null)
            ->joinLeft(array('l' => $this->getTable('eav/attribute_label')),
                $db->quoteInto("`l`.`attribute_id` = `a`.`attribute_id` AND `l`.`store_id` = ?", $schema->getStoreId()),
                null)
        ->columns($fields);

        if ($core->isManadevLayeredNavigationInstalled()) {
            $select
                ->joinInner(array('g' => $this->getTable('mana_filters/filter2')), '`g`.`code` = `a`.`attribute_code`', null)
                ->joinInner(array('f' => $this->getTable('mana_filters/filter2_store')),
                    $db->quoteInto("`f`.`global_id` = `g`.`id` AND `f`.`store_id` = ?", $schema->getStoreId()),
                    null);
        }

        $obsoleteCondition = "(`schema_id` = " . $schema->getId() . ") AND (`is_attribute_value` = 1) AND " .
            "(`type` = 'option')";
        if (isset($options['attribute_id'])) {
            $select->where('`a`.`attribute_id` = ?', $options['attribute_id']);
            $obsoleteCondition .= ' AND (`attribute_id` = ' . $options['attribute_id'] . ')';
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
        $db->exec($sql);
    }
}