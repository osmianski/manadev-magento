<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterAttributes
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * This class observes certain (defined in etc/config.xml) events in the whole system and provides public methods - 
 * handlers for these events.
 * @author Mana Team
 *
 */
class ManaPro_FilterAttributes_Model_Observer {
	// INSERT HERE: event handlers

    /**
     * Update stock_status, after product stock status change
     * @param Varien_Event_Observer $observer
     */
    public function saveStockStatus( $observer)
    {
        /* @var $product Mage_CatalogInventory_Model_Stock_Item */
        $product = $observer->getEvent()->getItem();
         Mage::getResourceSingleton('manapro_filterattributes/stockstatus')->process($this,
            array('product_id' => $product->getData('product_id')));
    }

    /**
     * Updates ratings after it is approved (handles event "review_save_commit_after")
     * @param Varien_Event_Observer $observer
     */
    public function reviewSaveAfter($observer) {
        /* @var $review Mage_Review_Model_Review */
        $review = $observer->getEvent()->getObject();

        Mage::getResourceSingleton('manapro_filterattributes/rating')->process($this,
            array('product_id' => $review->getData('entity_pk_value')));
    }

    /**
     * Updates ratings after it is approved (handles event "delete_save_commit_after")
     * @param Varien_Event_Observer $observer
     */
    public function reviewDeleteAfter($observer) {
        /* @var $review Mage_Review_Model_Review */
        $review = $observer->getEvent()->getObject();

        Mage::getResourceSingleton('manapro_filterattributes/rating')->process($this,
            array('product_id' => $review->getData('entity_pk_value')));
    }
}