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
abstract class Mana_Seo_Resource_UrlIndexer extends Mage_Core_Model_Mysql4_Abstract {
    /**
     * Resource initialization
     */
    protected function _construct() {
        $this->_setResource('core');
    }

    /**
     * @param Mana_Seo_Model_UrlIndexer $indexer
     * @param array $options
     */
    abstract public function process($indexer, $options);

    protected function getTargetTableName() {
        return $this->getTable('mana_seo/url');
    }

    /**
     * @return Mana_Seo_Model_Schema[]
     */
    protected function _getSchemas() {
        /* @var $dbHelper Mana_Db_Helper_Data */
        $dbHelper = Mage::helper('mana_db');

        /* @var $collection Mana_Db_Resource_Entity_Collection */
        $collection = $dbHelper->getResourceModel('mana_seo/schema/store_flat_collection');
        return $collection;
    }

    /**
     * @param string $expr
     * @param Mana_Seo_Model_Schema $schema
     * @return string
     */
    protected function _seoify($expr, $schema) {
        $expr = "LOWER($expr)";
        foreach ($schema->getSortedSymbols() as $symbol) {
            $expr = "REPLACE($expr, '". str_replace("'", "\\'", $symbol['symbol']) . "', '{$symbol['substitute']}')";
        }
        return $expr;
    }
}