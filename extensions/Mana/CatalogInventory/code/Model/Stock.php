<?php
/**
 * @category    Mana
 * @package     Mana_CatalogInventory
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */


/**
 * Modified stock behavior
 * @author Mana Team
 *
 */
class Mana_CatalogInventory_Model_Stock extends Mage_CatalogInventory_Model_Stock {
    protected function _construct() {
        $this->_init('mana_cataloginventory/stock');
    }

    protected function _prepareProductQtys($items) {
        $qtys = array();
        foreach ($items as $productId => $item) {
            if (empty($item['item'])) {
                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
            } else {
                $stockItem = $item['item'];
            }
            $canSubtractQty = $stockItem->getId() && $stockItem->canSubtractQty();
            if ($canSubtractQty && Mage::helper('catalogInventory')->isQty($stockItem->getTypeId())) {
                // MANA BEGIN
                if($this->helper()->isManadevProductFacesInstalled()) {
                    $m_pack_qty = Mage::getSingleton('cataloginventory/stock_item')->loadByProduct($productId)->getData('m_pack_qty');
                    $qtys[$productId] = $item['qty'] * $m_pack_qty;
                } else {
                    $qtys[$productId] = $item['qty'];
                }
                // MANA END
            }
        }

        return $qtys;
    }

    public function registerProductsSale($items)
    {
        $qtys = $this->_prepareProductQtys($items);

        // MANA BEGIN
        if ($this->helper()->isManadevProductFacesInstalled ()) {
            $representedProductIds = $this->_prepareRepresentingProductIds($qtys);
        }
        // MANA END
        $item = Mage::getModel('cataloginventory/stock_item');
        $this->_getResource()->beginTransaction();
        $stockInfo = $this->_getResource()->getProductsStock($this, array_keys($qtys), true);
        $fullSaveItems = array();
        foreach ($stockInfo as $itemInfo) {
            $item->setData($itemInfo);
            $item->setData('m_check_actual_qty', true);
            if (!$item->checkQty($qtys[$item->getProductId()])) {
                $this->_getResource()->commit();
                Mage::throwException(Mage::helper('cataloginventory')->__('Not all products are available in the requested quantity'));
            }
            $item->subtractQty($qtys[$item->getProductId()]);
            if (!$item->verifyStock() || $item->verifyNotification()) {
                $fullSaveItems[] = clone $item;
            }
        }
        $this->_getResource()->correctItemsQty($this, $qtys, '-');

        // MANA BEGIN
        if ($this->helper()->isManadevProductFacesInstalled()) {
            foreach ($representedProductIds as $representedProductId) {
                Mage::getResourceSingleton('manapro_productfaces/inventory')->updateRepresentingProducts($representedProductId, false);
                if ($this->helper()->isManadevFilterAttributesInstalled()) {
                    $options = array('product_id' => $representedProductId);
                    Mage::getResourceSingleton('manapro_filterattributes/stockStatus')->process($this, $options);
                }
            }
        }
        if ($this->helper()->isManadevFilterAttributesInstalled()) {
            foreach ($stockInfo as $itemInfo) {
                $item->setData($itemInfo);
                $options = array('product_id' => $item->getProductId());
                Mage::getResourceSingleton('manapro_filterattributes/stockStatus')->process($this, $options);
            }
        }
        /** @var ManaPro_ProductFaces_Model_Item $item */
        foreach($fullSaveItems as $item) {
            $item->load($item->getId());

            $typeId = $item->getTypeId();
            if ($productTypeId = $item->getProductTypeId()) {
                $typeId = $productTypeId;
            }

            $isQty = Mage::helper('catalogInventory')->isQty($typeId);

            if ($isQty) {
                if (!$item->verifyStock()) {
                    $item->setIsInStock(false)
                        ->setStockStatusChangedAutomaticallyFlag(true);
                }
            }

            // if qty is below notify qty, update the low stock date to today date otherwise set null
            $item->setLowStockDate(null);
            if ($item->verifyNotification()) {
                $item->setLowStockDate(Mage::app()->getLocale()->date(null, null, null, false)
                    ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT)
                );
            }

            $item->setStockStatusChangedAutomatically(0);
            if ($item->hasStockStatusChangedAutomaticallyFlag()) {
                $item->setStockStatusChangedAutomatically((int)$item->getStockStatusChangedAutomaticallyFlag());
            }
        }
        // MANA END
        $this->_getResource()->commit();
        return $fullSaveItems;
    }
    protected function _prepareRepresentingProductIds(&$qtys) {
    	$representedProductIds = array();
        foreach ($qtys as $id => $qty) {
            if ($representedProductId = Mage::getResourceSingleton('manapro_productfaces/link')->getRepresentedProductId($id)) {
            	$representedProductIds[$representedProductId] = $representedProductId;
            	if (!isset($qtys[$representedProductId])) {
            		$qtys[$representedProductId] = 0;
            	}
            	if ($representedProductId != $id) {
            		$qtys[$representedProductId] += $qty;
            		unset($qtys[$id]);
            	}
            }
        }
        return $representedProductIds;
    }
    public function revertProductsSale($items)
    {
        $qtys = $this->_prepareProductQtys($items);
        // MANA BEGIN
        if ($this->helper()->isManadevProductFacesInstalled()) {
            $representedProductIds = $this->_prepareRepresentingProductIds($qtys);
        }
        // MANA END
        $this->_getResource()->correctItemsQty($this, $qtys, '+');
        // MANA BEGIN
        if ($this->helper()->isManadevProductFacesInstalled()) {
            foreach ($representedProductIds as $representedProductId) {
                Mage::getResourceSingleton('manapro_productfaces/inventory')->updateRepresentingProducts($representedProductId, false);
                if ($this->helper()->isManadevFilterAttributesInstalled()) {
                    $options = array('product_id' => $representedProductId);
                    Mage::getResourceSingleton('manapro_filterattributes/stockStatus')->process($this, $options);
                }
            }
        }
        if ($this->helper()->isManadevFilterAttributesInstalled()) {
            foreach ($items as $productId => $item) {
                $options = array('product_id' => $productId);
                Mage::getResourceSingleton('manapro_filterattributes/stockStatus')->process($this, $options);
            }
        }
        // MANA END
        return $this;
    }
    public function registerItemSale(Varien_Object $item)
    {
        $productId = $item->getProductId();
        $representedProductId = null;
        if ($productId) {
        	// MANA BEGIN
            if ($this->helper()->isManadevProductFacesInstalled()) {
                if ($representedProductId = Mage::getResourceSingleton('manapro_productfaces/link')->getRepresentedProductId($productId)) {
        		    $productId = $representedProductId;
        	    }
            }
        	// MANA END
            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
            if (Mage::helper('catalogInventory')->isQty($stockItem->getTypeId())) {
                if ($item->getStoreId()) {
                    $stockItem->setStoreId($item->getStoreId());
                }
                if ($stockItem->checkQty($item->getQtyOrdered()) || Mage::app()->getStore()->isAdmin()) {
                    $stockItem->subtractQty($item->getQtyOrdered());
                    $stockItem->save();
                    // MANA BEGIN
                    if ($this->helper()->isManadevProductFacesInstalled()) {
                        if ($representedProductId) {
                            Mage::getResourceSingleton('manapro_productfaces/inventory')->updateRepresentingProducts($representedProductId);
                            if ($this->helper()->isManadevFilterAttributesInstalled()) {
                                $options = array('product_id' => $representedProductId);
                                Mage::getResourceSingleton('manapro_filterattributes/stockStatus')->process($this, $options);
                            }
                        }
                    }
                    if ($this->helper()->isManadevFilterAttributesInstalled()) {
                        $options = array('product_id' => $productId);
                        Mage::getResourceSingleton('manapro_filterattributes/stockStatus')->process($this, $options);
                    }
                    // MANA END
                }
            }
        }
        else {
            Mage::throwException(Mage::helper('cataloginventory')->__('Cannot specify product identifier for the order item.'));
        }
        return $this;
    }
    public function backItemQty($productId, $qty)
    {
        $representedProductId = null;

        // MANA BEGIN
        if ($this->helper()->isManadevProductFacesInstalled()) {
            if ($representedProductId = Mage::getResourceSingleton('manapro_productfaces/link')->getRepresentedProductId($productId)) {
                $productId = $representedProductId;
            }
        }
        // MANA END
    	$stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
        if ($stockItem->getId() && Mage::helper('catalogInventory')->isQty($stockItem->getTypeId())) {
            // MANA BEGIN
            if ($this->helper()->isManadevProductFacesInstalled()) {
                $qty *= $stockItem->getData('m_pack_qty');
            }
            // MANA END
            $stockItem->addQty($qty);
            if ($stockItem->getCanBackInStock() && $stockItem->getQty() > $stockItem->getMinQty()) {
                $stockItem->setIsInStock(true)
                    ->setStockStatusChangedAutomaticallyFlag(true);
            }
            $stockItem->save();
            // MANA BEGIN
            if ($this->helper()->isManadevProductFacesInstalled()) {
                if ($representedProductId) {
                    Mage::getResourceSingleton('manapro_productfaces/inventory')->updateRepresentingProducts($representedProductId);
                    if ($this->helper()->isManadevFilterAttributesInstalled()) {
                        $options = array('product_id' => $representedProductId);
                        Mage::getResourceSingleton('manapro_filterattributes/stockStatus')->process($this, $options);
                    }
                }
            }
            if ($this->helper()->isManadevFilterAttributesInstalled()) {
                $options = array('product_id' => $productId);
                Mage::getResourceSingleton('manapro_filterattributes/stockStatus')->process($this, $options);
            }
            // MANA END
        }

        return $this;
    }

    /**
     * @return Mana_CatalogInventory_Helper_Data
     */
    public function helper() {
        return Mage::helper('mana_cataloginventory');
    }
}