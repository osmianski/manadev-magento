<?php
/**
 * @category    Mana
 * @package     ManaPage_New
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPage_New_Block_Filter extends Mana_Page_Block_Filter {
    protected $_usedAttributes = array('news_from_date', 'news_to_date');

    protected function _prepareProductCollection() {
        /* @var $flat Mage_Catalog_Helper_Product_Flat */
        $flat = Mage::helper('catalog/product_flat');

        $todayDate = $this->getTodayDate();
//        $this->_productCollection
//            ->addAttributeToSelect('news_from_date', 'left')
//            ->addAttributeToSelect('news_to_date', 'left')
//            ->addAttributeToSelect('created_at');

        $conditions = array();

        /* @var $db Varien_Db_Adapter_Pdo_Mysql */
        $db = $this->_productCollection->getConnection();
        /* @var $res Mage_Core_Model_Resource */
        $res = Mage::getSingleton('core/resource');

        if (!$this->getIgnoreNewProducts()) {
            $news_from_date = $this->joinAttribute('news_from_date');
            $news_to_date = $this->joinAttribute('news_to_date');

            $conditions[] = $db->quoteInto("($news_from_date <= ? AND ($news_to_date >= ? OR $news_to_date IS NULL))", $todayDate);
        }
        if ($days = $this->getDaysProductsAreNew()) {
            $date = $this->getDate()->addDay(-$days)->toString(Varien_Date::DATE_INTERNAL_FORMAT);
            $conditions[] = $db->quoteInto("(e.created_at >= ?)", $date);
        }

        if (count($conditions)) {
            $this->_productCollection->getSelect()->where(implode(' OR ', $conditions));
        }
        return $this;
    }
}