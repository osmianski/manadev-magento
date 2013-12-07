<?php
/**
 * @category    Mana
 * @package     ManaPage_New
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * Generic helper functions for ManaPage_New module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class ManaPage_New_Helper_Data extends Mana_Page_Helper_Data
{
    protected $_newAttributes;

    /**
     * @param Mage_Core_Block_Abstract $block
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @return $this
     */
    public function getNewProductCollectionCondition($block, $collection) {
        $todayDate = $this->getTodayDate();

        $condition = array();

        /* @var $db Varien_Db_Adapter_Pdo_Mysql */
        $db = $collection->getConnection();

        if (!$block->getData('ignore_new_products')) {
            $news_from_date = $this->joinAttribute($collection, 'news_from_date', $this->getNewAttributes());
            $news_to_date = $this->joinAttribute($collection, 'news_to_date', $this->getNewAttributes());

            $condition[] = $db->quoteInto("($news_from_date <= ? AND ($news_to_date >= ? OR $news_to_date IS NULL))", $todayDate);
        }
        if ($days = $block->getData('days_products_are_new')) {
            $date = $this->getDate()->addDay(-$days)->toString(Varien_Date::DATE_INTERNAL_FORMAT);
            $createdAt = $this->joinField($collection, 'created_at');
            $condition[] = $db->quoteInto("$createdAt >= ?", $date);
        }

        return strtolower($block->getData('condition')) == 'or'
            ? implode(' OR ', $condition)
            : implode(' AND ', $condition);
    }

    /**
     * @param Mage_Core_Block_Abstract $block
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
     */
    public function getNewProductCollection($block) {
        $collection = $this->createProductCollection($block->getData('starting_from_product'), $block->getData('max_product_count'));
        $condition = $this->getNewProductCollectionCondition($block, $collection);
        if ($condition) {
            $collection->getSelect()->distinct()->where($condition);
        }

        return $collection;
    }
    public function getNewAttributes() {
        if (!$this->_newAttributes) {
            $this->_newAttributes = $this->getAttributes(array('news_from_date', 'news_to_date'));
        }

        return $this->_newAttributes;
    }
}