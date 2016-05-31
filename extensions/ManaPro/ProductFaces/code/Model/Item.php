<?php
/**
 * @category    Mana
 * @package     ManaPro_ProductFaces
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Modified stock item behavior
 * @author Mana Team
 *
 */
class ManaPro_ProductFaces_Model_Item extends Mage_CatalogInventory_Model_Stock_Item {
    protected function _getMQty() {
        return $this->getMRepresents() && !$this->getData('m_check_actual_qty') ? $this->getMRepresentedQty() : $this->getQty();
    }
    public function checkQty($qty)
    {
        if (!$this->getManageStock() || Mage::app()->getStore()->isAdmin()) {
            return true;
        }
        if ($this->_getMQty() - $this->getMinQty() - $qty < 0) {
            switch ($this->getBackorders()) {
                case Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NONOTIFY:
                case Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NOTIFY:
                    break;
                default:
                    return false;
                    break;
            }
        }
        return true;
    }

    public function checkQuoteItemQty($qty, $summaryQty, $origQty = 0) {
        $result = new Varien_Object();
        $result->setHasError(false);

        if (!is_numeric($qty)) {
            $qty = Mage::app()->getLocale()->getNumber($qty);
        }

        /**
         * Check quantity type
         */
        $result->setItemIsQtyDecimal($this->getIsQtyDecimal());

        if (!$this->getIsQtyDecimal()) {
            $result->setHasQtyOptionUpdate(true);
            $qty = intval($qty);

            /**
             * Adding stock data to quote item
             */
            $result->setItemQty($qty);

            if (!is_numeric($qty)) {
                $qty = Mage::app()->getLocale()->getNumber($qty);
            }
            $origQty = intval($origQty);
            $result->setOrigQty($origQty);
        }

        if ($this->getMinSaleQty() && $qty < $this->getMinSaleQty()) {
            $result->setHasError(true)
                ->setMessage(
                    Mage::helper('cataloginventory')->__('The minimum quantity allowed for purchase is %s.', $this->getMinSaleQty() * 1)
                )
                ->setErrorCode('qty_min')
                ->setQuoteMessage(Mage::helper('cataloginventory')->__('Some of the products cannot be ordered in requested quantity.'))
                ->setQuoteMessageIndex('qty');

            return $result;
        }

        if ($this->getMaxSaleQty() && $qty > $this->getMaxSaleQty()) {
            $result->setHasError(true)
                ->setMessage(
                    Mage::helper('cataloginventory')->__('The maximum quantity allowed for purchase is %s.', $this->getMaxSaleQty() * 1)
                )
                ->setErrorCode('qty_max')
                ->setQuoteMessage(Mage::helper('cataloginventory')->__('Some of the products cannot be ordered in requested quantity.'))
                ->setQuoteMessageIndex('qty');

            return $result;
        }

        $result->addData($this->checkQtyIncrements($qty)->getData());
        if ($result->getHasError()) {
            return $result;
        }

        if (!$this->getManageStock()) {
            return $result;
        }

        if (!$this->getIsInStock()) {
            $result->setHasError(true)
                ->setMessage(Mage::helper('cataloginventory')->__('This product is currently out of stock.'))
                ->setQuoteMessage(Mage::helper('cataloginventory')->__('Some of the products are currently out of stock'))
                ->setQuoteMessageIndex('stock');
            $result->setItemUseOldQty(true);

            return $result;
        }

        foreach($this->_getRepresentingProducts() as $representedProductId => $representingProducts) {
            $isManySkuProduct = false;
            $requestedQty = 0;

            foreach($representingProducts as $representingProductId => $representingQty) {
                $requestedQty += $representingQty;
                if($representingProductId == $this->getProductId()) {
                    $isManySkuProduct = true;
                }
            }

            if($isManySkuProduct) {
                $representedStock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($representedProductId);
                $representedStock->setData('m_check_actual_qty', true);
                if(!$representedStock->checkQty($requestedQty)) {
                    $message = Mage::helper('cataloginventory')->__('The requested quantity for "%s" is not available.', $this->getProductName());
                    $result->setHasError(true)
                        ->setMessage($message)
                        ->setQuoteMessage($message)
                        ->setQuoteMessageIndex('qty');

                    return $result;
                }
            }
        }


        if (!$this->checkQty($summaryQty) || !$this->checkQty($qty)) {
            $message = Mage::helper('cataloginventory')->__('The requested quantity for "%s" is not available.', $this->getProductName());
            $result->setHasError(true)
                ->setMessage($message)
                ->setQuoteMessage($message)
                ->setQuoteMessageIndex('qty');

            return $result;
        }
        else {
            if (($this->_getMQty() - $summaryQty) < 0) {
                if ($this->getProductName()) {
                    if ($this->getIsChildItem()) {
                        $backorderQty = ($this->_getMQty() > 0) ? ($summaryQty - $this->_getMQty()) * 1 : $qty * 1;
                        if ($backorderQty > $qty) {
                            $backorderQty = $qty;
                        }

                        $result->setItemBackorders($backorderQty);
                    }
                    else {
                        $orderedItems = $this->getOrderedItems();
                        $itemsLeft = ($this->_getMQty() > $orderedItems) ? ($this->_getMQty() - $orderedItems) * 1 : 0;
                        $backorderQty = ($itemsLeft > 0) ? ($qty - $itemsLeft) * 1 : $qty * 1;

                        if ($backorderQty > 0) {
                            $result->setItemBackorders($backorderQty);
                        }
                        $this->setOrderedItems($orderedItems + $qty);
                    }

                    if ($this->getBackorders() == Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NOTIFY) {
                        if (!$this->getIsChildItem()) {
                            $result->setMessage(
                                Mage::helper('cataloginventory')->__('This product is not available in the requested quantity. %s of the items will be backordered.', ($backorderQty * 1))
                            );
                        }
                        else {
                            $result->setMessage(
                                Mage::helper('cataloginventory')->__('"%s" is not available in the requested quantity. %s of the items will be backordered.', $this->getProductName(), ($backorderQty * 1))
                            );
                        }
                    }
                    elseif (Mage::app()->getStore()->isAdmin()) {
                        $result->setMessage(
                            Mage::helper('cataloginventory')->__('The requested quantity for "%s" is not available.', $this->getProductName())
                        );
                    }
                }
            }
            else {
                if (!$this->getIsChildItem()) {
                    $this->setOrderedItems($qty + (int)$this->getOrderedItems());
                }
            }
        }

        return $result;
    }

    /**
     * Chceck if item should be in stock or out of stock based on $qty param of existing item qty
     *
     * @param float|null $qty
     * @return bool true - item in stock | false - item out of stock
     */
    public function verifyStock($qty = null)
    {
        if ($qty === null) {
            // MANAdev: start
            $qty = $this->_getMQty();
            // MANAdev: end
        }
        if ($this->getBackorders() == Mage_CatalogInventory_Model_Stock::BACKORDERS_NO && $qty <= $this->getMinQty()) {
            return false;
        }
        return true;
    }

    /**
     * @return array
     */
    protected function _getRepresentingProducts() {
        $result = Mage::registry("m_productfaces_represented_stocks");
        if (!is_null($result)) {
            /** @var ManaPro_ProductFaces_Resource_Link $linkResource */
            $linkResource = Mage::getResourceModel('manapro_productfaces/link');
            $result = array();

            foreach (Mage::getSingleton('checkout/session')->getQuote()->getAllItems() as $item) {
                $representedProductId = $linkResource->getRepresentedProductId($item->getProductId());

                foreach ($linkResource->getRepresentingProductsAndOptions($representedProductId) as $data) {
                    if ($item->getProductId() == $data['linked_product_id']) {
                        $result[$representedProductId][$item->getProductId()] = $item->getQty() * $data['m_pack_qty'];
                        break;
                    }
                }
            }

            Mage::register("m_productfaces_represented_stocks", $result);
        } else {
            $result = array();
        }

        return $result;
    }

}