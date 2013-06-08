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
     * @param Mana_Seo_Model_Schema $schema
     * @param array $options
     */
    public function process($indexer, $schema, $options) {
        $db = $this->_getWriteAdapter();

        /* @var $seo Mana_Seo_Helper_Url_Filter */
        $seo = Mage::helper('mana_seo');

        $urlKeyExpr = $schema->getUseFilterLabels()
            ? ($seo->isManadevLayeredNavigationInstalled()
                ? $this->_seoify('`f`.`name`', $schema)
                : $this->_seoify('COALESCE(`l`.`value`, `a`.`frontend_label`)', $schema)
            )
            : "REPLACE(LOWER(`a`.`attribute_code`), '_', '-')";
        $fields = array(
            'url_key' => new Zend_Db_Expr($urlKeyExpr),
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
        );

        /* @var $select Varien_Db_Select */
        $select = $this->_getFilterableAttributeSelect($db)
            ->joinLeft(array('l' => $this->getTable('eav/attribute_label')),
                $db->quoteInto("`l`.`attribute_id` = `a`.`attribute_id` AND `l`.`store_id` = ?", $schema->getStoreId()),
                null);

        if ($seo->isManadevLayeredNavigationInstalled()) {
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
        $db->raw_query($sql);
    }
}