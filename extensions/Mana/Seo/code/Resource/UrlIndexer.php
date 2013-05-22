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
            $expr = "REPLACE($expr, {$this->_quote($symbol['symbol'])}, {$this->_quote($symbol['substitute'])})";
        }
        return $expr;
    }

    protected function _quote($s) {
        return $this->_getReadAdapter()->quote($s);
    }

    /**
     * @param Varien_Db_Adapter_Pdo_Mysql $db
     * @return Varien_Db_Select
     */
    protected function _getFilterableAttributeSelect($db) {
        return $db->select()
            ->from(array('a' => $this->getTable('eav/attribute')), null)
            ->joinInner(array('t' => $this->getTable('eav/entity_type')), "`t`.`entity_type_id` = `a`.`entity_type_id` AND `t`.`entity_type_code` = 'catalog_product'", null)
            ->joinInner(array('ca' => $this->getTable('catalog/eav_attribute')), "`ca`.`attribute_id` = `a`.`attribute_id`", null)
            ->where("`ca`.`is_filterable` <> 0");
    }
}