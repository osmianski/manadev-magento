<?php
/**
 * @category    Mana
 * @package     Local_Manadev
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Local_Manadev_Model_Discount extends Mage_SalesRule_Model_Validator {
    public function process(Mage_Sales_Model_Quote_Item_Abstract $item) {
        if ($item->hasMOriginalDiscountPercent()) {
            return $this->_processOriginalDiscountAmount($item);
        }
        else {
            return parent::process($item);
        }
    }
    protected function _processOriginalDiscountAmount(Mage_Sales_Model_Quote_Item_Abstract $item) {
        $item->setDiscountPercent($item->getMOriginalDiscountPercent());

        $quote = $item->getQuote();
        $address = $this->_getAddress($item);

        $itemPrice = $this->_getItemPrice($item);
        $baseItemPrice = $this->_getItemBasePrice($item);
        $itemOriginalPrice = $this->_getItemOriginalPrice($item);
        $baseItemOriginalPrice = $this->_getItemBaseOriginalPrice($item);

        if ($itemPrice < 0) {
            return $this;
        }

        $qty = $item->getQty();
        $_rulePct = $item->getMOriginalDiscountPercent() / 100;
        $item->setDiscountAmount($quote->getStore()->roundPrice(
            ($qty * $itemPrice - $item->getDiscountAmount()) * $_rulePct));
        $item->setBaseDiscountAmount($quote->getStore()->roundPrice(
            ($qty * $baseItemPrice - $item->getBaseDiscountAmount()) * $_rulePct));
        //get discount for original price
        $item->setOriginalDiscountAmount($quote->getStore()->roundPrice(
            ($qty * $itemOriginalPrice - $item->getDiscountAmount()) * $_rulePct));
        $item->setBaseOriginalDiscountAmount($quote->getStore()->roundPrice(
            ($qty * $baseItemOriginalPrice - $item->getDiscountAmount()) * $_rulePct));

        return $this;
    }
}