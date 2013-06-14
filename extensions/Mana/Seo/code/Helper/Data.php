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
    protected $_pageTypes;
    protected $_activeSchemas = array();
    protected $_parameterUrls = array();

    /**
     * @return Mana_Seo_Helper_PageType[]
     */
    public function getPageTypes() {
        if (!$this->_pageTypes) {
            /* @var $core Mana_Core_Helper_Data */
            $core = Mage::helper('mana_core');

            $result = array();

            foreach ($core->getSortedXmlChildren(Mage::getConfig()->getNode('mana_seo'), 'page_types') as $key => $pageTypeXml) {
                /* @var $pageType Mana_Seo_Helper_PageType */
                $pageType = Mage::helper((string)$pageTypeXml->helper);
                $pageType->setCode($key);
                $result[$key] = $pageType;
            }
            $this->_pageTypes = $result;
        }

        return $this->_pageTypes;
    }

    public function getPageType($type) {
        $pageTypes = $this->getPageTypes();
        return $pageTypes[$type];
    }

    public function isManadevLayeredNavigationInstalled() {
        return $this->isModuleEnabled('Mana_Filters');
    }

    public function isManadevSeoLayeredNavigationInstalled() {
        return $this->isModuleEnabled('ManaPro_FilterSeoLinks');
    }

    public function isManadevAttributePageInstalled() {
        return $this->isModuleEnabled('Mana_AttributePage');
    }

    /**
     * @param int $storeId
     * @return Mana_Seo_Model_Schema | false
     */
    public function getActiveSchema($storeId) {
        if (!isset($this->_activeSchemas[$storeId])) {
            /* @var $dbHelper Mana_Db_Helper_Data */
            $dbHelper = Mage::helper('mana_db');

            /* @var $collection Mana_Db_Resource_Entity_Collection */
            $collection = $dbHelper->getResourceModel('mana_seo/schema/store_flat_collection');
            $collection
                ->setStoreFilter($storeId)
                ->addFieldToFilter('status', Mana_Seo_Model_Schema::STATUS_ACTIVE);

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

    #region Test Helpers
    public function clearParameterUrlCache() {
        $this->_parameterUrls = array();

        return $this;
    }

    #endregion
}