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
            'customer_email' => $this->_getCustomerEmailExpr(),
        ));
    }

    /**
     * @param Mage_Core_Model_Resource_Db_Collection_Abstract $collection
     */
    public function addCustomerEmailCollectionFilter($collection, $condition) {
        $collection->getSelect()->where("({$this->_getCustomerEmailExpr()}) LIKE {$condition['like']}");
    }

    protected function _getCustomerEmailExpr() {
        return new Zend_Db_Expr("(
            SELECT `main_order`.`customer_email`
            FROM {$this->getTable('sales/order')} AS `main_order`
            WHERE `main_table`.`entity_id` = `main_order`.`entity_id`
        )");
    }
    /**
     * Invoked during resource model creation process, this method associates this resource model with model class
     * and with DB table name
     */
	protected function _construct() {
        $this->_setMainTable('downloadable/link_purchased_item');
    }
}