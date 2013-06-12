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
class Mana_Seo_Resource_UrlIndexer_Value extends Mana_Seo_Resource_UrlIndexer {
    /**
     * @param Mana_Seo_Model_UrlIndexer $indexer
     * @param Mana_Seo_Model_Schema $schema
     * @param array $options
     */
    public function process($indexer, $schema, $options) {
        $db = $this->_getWriteAdapter();

        /* @var $seo Mana_Seo_Helper_Url_Filter */
        $seo = Mage::helper('mana_seo');

        $urlKeyExpr = $this->_seoify("COALESCE(vs.value, vg.value)", $schema);
        $fields = array(
            'url_key' => new Zend_Db_Expr($urlKeyExpr),
            'type' => new Zend_Db_Expr("'option'"),
            'is_page' => new Zend_Db_Expr('0'),
            'is_parameter' => new Zend_Db_Expr('0'),
            'is_attribute_value' => new Zend_Db_Expr('1'),
            'is_category_value' => new Zend_Db_Expr('0'),
            'include_filter_name' => new Zend_Db_Expr($seo->isManadevLayeredNavigationInstalled()
                ? "IF(`f`.include_in_url = '". Mana_Seo_Model_Source_IncludeInUrl::ALWAYS."', 1, ".
                    "IF(`f`.include_in_url = '" . Mana_Seo_Model_Source_IncludeInUrl::NEVER . "', 0, ".
                    "{$schema->getIncludeFilterName()}))"
                : $schema->getIncludeFilterName()),
            'schema_id' => new Zend_Db_Expr($schema->getId()),
            'option_id' => new Zend_Db_Expr('`o`.`option_id`'),
            'unique_key' => new Zend_Db_Expr("CONCAT(`o`.`option_id`, '-', $urlKeyExpr)"),
            'status' => new Zend_Db_Expr("'" . Mana_Seo_Model_Url::STATUS_ACTIVE . "'"),
        );

        /* @var $select Varien_Db_Select */
        $select = $this->_getFilterableAttributeSelect($db)
            ->joinInner(array('o' => $this->getTable('eav/attribute_option')), "`o`.`attribute_id` = `a`.`attribute_id`", null)
            ->joinLeft(array('vg' => $this->getTable('eav/attribute_option_value')), 'o.option_id = vg.option_id AND vg.store_id = 0', null)
            ->joinLeft(array('vs' => $this->getTable('eav/attribute_option_value')),
                $db->quoteInto('o.option_id = vs.option_id AND vs.store_id = ?', $schema->getStoreId()),
                null)
            ->columns($fields);

        if ($seo->isManadevLayeredNavigationInstalled()) {
            $select
                ->joinInner(array('g' => $this->getTable('mana_filters/filter2')), '`g`.`code` = `a`.`attribute_code`', null)
                ->joinInner(array('f' => $this->getTable('mana_filters/filter2_store')),
                    $db->quoteInto("`f`.`global_id` = `g`.`id` AND `f`.`store_id` = ?", $schema->getStoreId()),
                    null);
        }

        // convert SELECT into UPDATE which acts as INSERT on DUPLICATE unique keys
        $sql = $select->insertFromSelect($this->getTargetTableName(), array_keys($fields));

        // run the statement
        $db->raw_query($sql);
    }
}