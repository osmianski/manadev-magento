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
class Mana_Seo_Helper_UrlKeyProvider_Database extends Mana_Seo_Helper_UrlKeyProvider {
    /**
     * @param string[] $candidates
     * @param int $storeId
     * @param bool $isPage
     * @param bool $isParameter
     * @param bool $isFirstValue
     * @param bool $isMultipleValue
     * @param Mana_Seo_Model_Url[] $activeUrlKeys
     * @param Mana_Seo_Model_Url[] $obsoleteUrlKeys
     */
    public function getUrlKeys($candidates, $storeId, $isPage, $isParameter, $isFirstValue,
        $isMultipleValue, &$activeUrlKeys, &$obsoleteUrlKeys)
    {
        $activeUrlKeys = array();
        $obsoleteUrlKeys = array();

        $collection = $this->_prepareCollection($candidates, $storeId, $isPage, $isParameter, $isFirstValue,
            $isMultipleValue);
        foreach ($collection as $urlKey) {
            /* @var $urlKey Mana_Seo_Model_Url */
            if ($urlKey->getStatus() == Mana_Seo_Model_Url::STATUS_ACTIVE) {
                $activeUrlKeys[] = $urlKey;
            }
            else {
                $obsoleteUrlKeys[] = $urlKey;
            }
        }
    }

    protected function _prepareCollection($candidates, $storeId, $isPage, $isParameter, $isFirstValue,
        $isMultipleValue)
    {
        /* @var $dbHelper Mana_Db_Helper_Data */
        $dbHelper = Mage::helper('mana_db');

        /* @var $collection Mana_Seo_Resource_Url_Collection */
        $collection = $dbHelper->getResourceModel('mana_seo/url_collection');
        $collection
            ->addOptionAttributeIdAndCodeToSelect()
            ->setStoreFilter($storeId)
            ->addFieldToFilter('url_key', array('in' => $candidates))
            ->addFieldToFilter('status', array(
                'in' => array(
                    Mana_Seo_Model_Url::STATUS_ACTIVE,
                    Mana_Seo_Model_Url::STATUS_OBSOLETE
                )
            ));

        $parserConditions = array();
        if ($isPage) {
            $parserConditions[] = "(`main_table`.`is_page` = 1)";
        }
        if ($isParameter) {
            $parserConditions[] = "(`main_table`.`is_parameter` = 1)";
        }
        if ($isFirstValue) {
            $parserConditions[] = "(`main_table`.`is_value` = 1)";
        }
        if ($isMultipleValue) {
            $parserConditions[] = "(`main_table`.`is_multiple_value` = 1)";
        }
        if (count($parserConditions)) {
            $collection->getSelect()->where(new Zend_Db_Expr(implode(' OR ', $parserConditions)));
        }
        return $collection;
    }
}