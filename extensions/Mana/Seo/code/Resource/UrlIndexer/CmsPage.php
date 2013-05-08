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
class Mana_Seo_Resource_UrlIndexer_CmsPage extends Mana_Seo_Resource_UrlIndexer {
    /**
     * @param Mana_Seo_Model_UrlIndexer $indexer
     * @param array $options
     */
    public function process($indexer, $options) {
        $db = $this->_getWriteAdapter();

        $fields = array(
            'url_key' => new Zend_Db_Expr('`p`.`identifier`'),
            'type' => new Zend_Db_Expr("'mana_seo/url_cmsPage'"),
            'is_page' => new Zend_Db_Expr(1),
            'supports_parameters' => new Zend_Db_Expr(1),
            'is_parameter' => new Zend_Db_Expr(0),
            'is_value' => new Zend_Db_Expr(0),
            'store_id' => new Zend_Db_Expr('`s`.`store_id`'),
            'cms_page_id' => new Zend_Db_Expr('`p`.`page_id`'),
            'unique_key' => new Zend_Db_Expr("CONCAT(`p`.`page_id`)"),
            'status' => new Zend_Db_Expr("IF(`p`.`is_active`, '" .
                Mana_Seo_Model_Url::STATUS_ACTIVE . "', '".
                Mana_Seo_Model_Url::STATUS_DISABLED . "')"),
        );

        /* @var $select Varien_Db_Select */
        $select = $db->select()
            ->from(array('p' => $this->getTable('cms/page')), null)
            ->joinInner(array('s' => $this->getTable('cms/page_store')), '`p`.`page_id` = `s`.`page_id`', null)
            ->columns($fields);

        // convert SELECT into UPDATE which acts as INSERT on DUPLICATE unique keys
        $sql = $select->insertFromSelect($this->getTargetTableName(), array_keys($fields));

        // run the statement
        $db->query($sql);
    }
}