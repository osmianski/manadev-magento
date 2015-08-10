<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Resource_Order extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * @param Mage_Core_Model_Resource_Db_Collection_Abstract $collection
     */
    public function addEmailsToCollection($collection) {
        $collection->getSelect()->columns(array(
            'customer_email' => new Zend_Db_Expr("(
                SELECT `main_order`.`customer_email`
                FROM {$this->getTable('sales/order')} AS `main_order`
                WHERE `main_table`.`entity_id` = `main_order`.`entity_id`
            )"),
        ));
    }

    /**
     * Invoked during resource model creation process, this method associates this resource model with model class
     * and with DB table name
     */
	protected function _construct() {
        $this->_setMainTable('downloadable/link_purchased_item');
    }

    /**
     * @param Mage_Core_Model_Resource_Db_Collection_Abstract $collection
     */
    public function addDownloadStatusToCollection($collection) {
        $availableDownloadCount = new Zend_Db_Expr("(
            SELECT COUNT(*)
            FROM {$this->getMainTable()} AS `m_link`
            INNER JOIN {$this->getTable('sales/order_item')} AS `m_item`
                ON `m_item`.`item_id` = ``.`m_link`.`order_item_id`
            WHERE `m_link`.`status` = 'available' AND `m_item`.`order_id` = `main_table`.`entity_id`
        )");
        $expiredDownloadCount = new Zend_Db_Expr("(
            SELECT COUNT(*)
            FROM {$this->getMainTable()} AS `m_link`
            INNER JOIN {$this->getTable('sales/order_item')} AS `m_item`
                ON `m_item`.`item_id` = ``.`m_link`.`order_item_id`
            WHERE `m_link`.`status` = 'expired' AND `m_item`.`order_id` = `main_table`.`entity_id`
        )");
        $collection->getSelect()->columns(array(
            'download_status' => new Zend_Db_Expr("
                IF ({$availableDownloadCount} > 0,
                    IF ({$expiredDownloadCount} > 0, 'partially_available', 'available'),
                    IF({$expiredDownloadCount} > 0, 'not_available', 'n_a')
                )
            "),
        ));
    }
}