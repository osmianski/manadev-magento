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

    public function prepareProductCollection() {
        $todayDate = $this->getTodayDate();

        $condition = array();

        /* @var $db Varien_Db_Adapter_Pdo_Mysql */
        $db = $this->_productCollection->getConnection();

        if (!$this->getIgnoreNewProducts()) {
            $news_from_date = $this->joinAttribute('news_from_date');
            $news_to_date = $this->joinAttribute('news_to_date');

            $condition[] = $db->quoteInto("($news_from_date <= ? AND ($news_to_date >= ? OR $news_to_date IS NULL))", $todayDate);
        }
        if ($days = $this->getDaysProductsAreNew()) {
            $date = $this->getDate()->addDay(-$days)->toString(Varien_Date::DATE_INTERNAL_FORMAT);
            $createdAt = $this->joinField('created_at');
            $condition[] = $db->quoteInto("$createdAt >= ?", $date);
        }

        $this->_condition = strtolower($this->getOperation()) == 'or'
            ? implode(' OR ', $condition)
            : implode(' AND ', $condition);

        return $this;
    }
}