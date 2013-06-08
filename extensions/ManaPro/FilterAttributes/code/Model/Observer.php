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
    public function saveStockStatus($observer) {
        $product = $observer->getEvent()->getProduct();
 /*       if ($product instanceof Mage_Catalog_Model_Product) {
            $productId = intval($product->getId());
            if (!isset($this->_stockItemsArray[$productId])) {
                $this->_stockItemsArray[$productId] = Mage::getModel('cataloginventory/stock_item');
            }
            $productStockItem = $this->_stockItemsArray[$productId];
            $productStockItem->assignProduct($product);
        }
        return $this;
        }
*/
        return $this;
    }
}