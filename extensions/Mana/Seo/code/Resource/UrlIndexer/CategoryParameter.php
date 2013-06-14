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
class Mana_Seo_Resource_UrlIndexer_CategoryParameter extends Mana_Seo_Resource_UrlIndexer {
    /**
     * @param Mana_Seo_Model_UrlIndexer $indexer
     * @param Mana_Seo_Model_Schema $schema
     * @param array $options
     */
    public function process($indexer, $schema, $options) {
        $db = $this->_getWriteAdapter();

        Mage::app()->getLocale()->emulate($schema->getStoreId());
        $defaultLabel = Mage::helper('catalog')->__('Category');
        Mage::app()->getLocale()->revert();

        /* @var $seo Mana_Seo_Helper_Url_Filter */
        $seo = Mage::helper('mana_seo');

        $urlKeyExpr = $seo->isManadevLayeredNavigationInstalled()
            ? ($schema->getUseFilterLabels() ? $this->_seoify('`f`.`name`', $schema) : "'category'")
            : ($schema->getUseFilterLabels() ? $this->_seoify($defaultLabel, $schema) : "'category'");
        $fields = array(
            'url_key' => new Zend_Db_Expr($urlKeyExpr),
            'internal_name' => new Zend_Db_Expr("'cat'"),
            'position' => new Zend_Db_Expr($seo->isManadevLayeredNavigationInstalled() ? '`f`.`position`': "-1"),
            'type' => new Zend_Db_Expr("'" . Mana_Seo_Model_ParsedUrl::PARAMETER_CATEGORY . "'"),
            'is_page' => new Zend_Db_Expr('0'),
            'is_parameter' => new Zend_Db_Expr('1'),
            'is_attribute_value' => new Zend_Db_Expr('0'),
            'is_category_value' => new Zend_Db_Expr('0'),
            'schema_id' => new Zend_Db_Expr($schema->getId()),
            'unique_key' => new Zend_Db_Expr($urlKeyExpr),
            'status' => new Zend_Db_Expr("'".($schema->getRedirectToSubcategory()
                ? Mana_Seo_Model_Url::STATUS_OBSOLETE
                : Mana_Seo_Model_Url::STATUS_ACTIVE)."'"),
        );

        if ($seo->isManadevLayeredNavigationInstalled()) {
            /* @var $select Varien_Db_Select */
            $select = $db->select()
                ->from(array('g' => $this->getTable('mana_filters/filter2')), null)
                ->joinInner(array('f' => $this->getTable('mana_filters/filter2_store')),
                    $db->quoteInto("`f`.`global_id` = `g`.`id` AND `f`.`store_id` = ?", $schema->getStoreId()),
                    null)
                ->where("`g`.`type` = ?", 'category');

            $select->columns($fields);

            // convert SELECT into UPDATE which acts as INSERT on DUPLICATE unique keys
            $sql = $select->insertFromSelect($this->getTargetTableName(), array_keys($fields));
        }
        else {
            $sql = $this->insert($this->getTargetTableName(), $fields);
        }

        // run the statement
        $db->raw_query($sql);
    }
}