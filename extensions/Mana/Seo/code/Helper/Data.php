<?php
/** 
 * @category    Mana
 * @package     Mana_Seo
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * Generic helper functions for Mana_Seo module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class Mana_Seo_Helper_Data extends Mage_Core_Helper_Abstract {
    protected $_activeSchemas = array();
    protected $_parameterUrls = array();
    protected $_categoryPaths = array();

    /**
     * @return Mana_Seo_Helper_PageType[]
     */
    public function getPageTypes() {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper('mana_core');

        return $core->getPageTypes('seo_helper');
    }

    /**
     * @param string $type
     * @return Mana_Seo_Helper_PageType
     */
    public function getPageType($type) {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper('mana_core');

        return $core->getPageType($type, 'seo_helper');
    }

    /**
     * @param int $storeId
     * @param bool $flat
     * @return Mana_Seo_Model_Schema | false
     */
    public function getActiveSchema($storeId, $flat = true) {
        if (!isset($this->_activeSchemas[$storeId])) {
            /* @var $dbHelper Mana_Db_Helper_Data */
            $dbHelper = Mage::helper('mana_db');

            /* @var $collection Mana_Db_Resource_Entity_Collection */
            if ($storeId != Mage_Core_Model_App::ADMIN_STORE_ID) {
                $collection = $flat
                    ? $dbHelper->getResourceModel('mana_seo/schema/store_flat_collection')
                    : $dbHelper->getResourceModel('mana_seo/schema/store_collection');
                $collection
                    ->setStoreFilter($storeId)
                    ->addFieldToFilter('status', Mana_Seo_Model_Schema::STATUS_ACTIVE);
            }
            else {
                $collection = $flat
                    ? $dbHelper->getResourceModel('mana_seo/schema/flat_collection')
                    : $dbHelper->getResourceModel('mana_seo/schema/global_collection');
                $collection
                    ->addFieldToFilter('status', Mana_Seo_Model_Schema::STATUS_ACTIVE);
            }

            foreach ($collection as $schema) {
                $this->_activeSchemas[$storeId] = $schema;
                break;
            }

            if (!isset($this->_activeSchemas[$storeId])) {
                $this->_activeSchemas[$storeId] = false;

            }
        }
        return $this->_activeSchemas[$storeId];
    }

    /**
     * @param Mana_Seo_Model_Schema $schema
     */
    public function getParameterUrls($schema) {
        if (!isset($this->_parameterUrls[$schema->getId()])) {
            $urls = array();
            $ids = array();
            foreach ($this->getUrlCollection($schema, Mana_Seo_Resource_Url_Collection::TYPE_PARAMETER) as $url) {
                /* @var $url Mana_Seo_Model_Url */
                if (!isset($urls[$url->getInternalName()])) {
                    $urls[$url->getInternalName()] = $url;
                }
                else {
                    $ids[] = $url->getId();
                }
            }
            if (count($ids)) {
                /* @var $logger Mana_Core_Helper_Logger */
                $logger = Mage::helper('mana_core/logger');
                $logger->logSeoUrl(sprintf('NOTICE: Multiple parameter URL keys found for one match request, taking first one. All URL key ids: %s', implode($ids)));
            }
            $this->_parameterUrls[$schema->getId()] = $urls;
        }
        return $this->_parameterUrls[$schema->getId()];
    }

    /**
     * @param Mana_Seo_Model_Schema $schema
     * @param int $type
     * @return Mana_Seo_Resource_Url_Collection
     */
    public function getUrlCollection($schema, $type) {
        /* @var $dbHelper Mana_Db_Helper_Data */
        $dbHelper = Mage::helper('mana_db');

        /* @var $collection Mana_Seo_Resource_Url_Collection */
        $collection = $dbHelper->getResourceModel('mana_seo/url_collection');
        $collection
            ->setSchemaFilter($schema)
            ->addTypeFilter($type)
            ->addFieldToFilter('status', Mana_Seo_Model_Url::STATUS_ACTIVE);

        return $collection;
    }

    public function seoifyExpr($expr, $schema = null) {
        if (!$schema) {
            $schema = $this->getActiveSchema(Mage::app()->getStore()->getId());
        }
        $res = Mage::getSingleton('core/resource');
        $db = $res->getConnection('read');

        $expr = "LOWER($expr)";
        foreach ($schema->getSortedSymbols() as $symbol) {
            $expr = "REPLACE($expr, {$db->quote($symbol['symbol'])}, {$db->quote($symbol['substitute'])})";
        }

        return $expr;
    }

    #region Test Helpers
    public function clearParameterUrlCache() {
        $this->_parameterUrls = array();

        return $this;
    }

    public function getCategoryPath($id) {
        if (!isset($this->_categoryPaths[$id])) {
            /* @var $res Mage_Core_Model_Resource */
            $res = Mage::getSingleton('core/resource');
            $db = $res->getConnection('read');
            $this->_categoryPaths[$id] = $db->fetchOne($db->select()
                ->from($res->getTableName('catalog/category'), 'path')
                ->where('entity_id = ?', $id));
        }
        return $this->_categoryPaths[$id];
    }

    #endregion
}