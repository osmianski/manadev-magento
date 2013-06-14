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
class Mana_Seo_Resource_UrlIndexer_Toolbar extends Mana_Seo_Resource_UrlIndexer {
    /**
     * @param Mana_Seo_Model_UrlIndexer $indexer
     * @param Mana_Seo_Model_Schema $schema
     * @param array $options
     */
    public function process($indexer, $schema, $options) {
        $db = $this->_getWriteAdapter();

        foreach ($schema->getJson('toolbar_url_keys') as $urlKey) {
            $urlKeyExpr = "'".$urlKey['name']. "'";
            $fields = array(
                'url_key' => new Zend_Db_Expr($urlKeyExpr),
                'type' => new Zend_Db_Expr("'" . Mana_Seo_Model_ParsedUrl::PARAMETER_TOOLBAR . "'"),
                'is_page' => new Zend_Db_Expr('0'),
                'is_parameter' => new Zend_Db_Expr('1'),
                'is_attribute_value' => new Zend_Db_Expr('0'),
                'is_category_value' => new Zend_Db_Expr('0'),
                'schema_id' => new Zend_Db_Expr($schema->getId()),
                'unique_key' => new Zend_Db_Expr($urlKeyExpr),
                'internal_name' => new Zend_Db_Expr("'" . $urlKey['internal_name'] . "'"),
                'position' => new Zend_Db_Expr($urlKey['position']),
                'status' => new Zend_Db_Expr("'" . Mana_Seo_Model_Url::STATUS_ACTIVE . "'"),
            );

            $sql = $this->insert($this->getTargetTableName(), $fields);

            // run the statement
            $db->raw_query($sql);
        }
    }
}