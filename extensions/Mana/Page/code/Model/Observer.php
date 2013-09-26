<?php
/**
 * @category    Mana
 * @package     Mana_Page
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * This class observes certain (defined in etc/config.xml) events in the whole system and provides public methods -
 * handlers for these events.
 * @author Mana Team
 *
 */
class Mana_Page_Model_Observer {
    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "m_preserve_product_collection_where_clause")
     * @param Varien_Event_Observer $observer
     */
    public function preserveEntityIdFilters($observer) {
        /* @var $where array */ $where = $observer->getEvent()->getWhere();
        /* @var $preserved Varien_Object */ $preserved = $observer->getEvent()->getPreserved();
        $result = $preserved->getPreserved();

        foreach ($where as $key => $condition) {
            if (strpos($condition, 'e.entity_id = ') !== false ||
                strpos($condition, '`e`.`entity_id` = ') !== false ||
                strpos($condition, '`e`.`entity_id` IN ') !== false ||
                strpos($condition, '`mp_') !== false)
            {
                $result[$key] = $key;
            }
        }

        $preserved->setPreserved($result);
    }
}