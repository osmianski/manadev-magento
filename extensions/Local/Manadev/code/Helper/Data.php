<?php
/**
 * @category    Mana
 * @package     Local_Manadev
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/* BASED ON SNIPPET: New Module/Helper/Data.php */
/**
 * Generic helper functions for Local_Manadev module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class Local_Manadev_Helper_Data extends Mage_Core_Helper_Abstract {
    const VAT_METHOD_1_ALWAYS_21 = 'v1_always_21';
    const VAT_METHOD_2_NOT_PAYER = 'v2_not_payer';
    const VAT_METHOD_3_AS_SPECIFIED_IN_RULES = 'v3_as_specified_in_rules';
    protected function _vatMethod($date) {
        if ($date['date'] > '2012-05-01') {
            return self::VAT_METHOD_3_AS_SPECIFIED_IN_RULES;
        } elseif ($date['date'] > '2011-09-14') {
            return self::VAT_METHOD_2_NOT_PAYER;
        }
        else {
            return self::VAT_METHOD_1_ALWAYS_21;
        }
    }

    protected function _vatPercent($document, $item, $vatMethod, $isVatValid) {
        $vatPercent = 0;
        switch ($vatMethod) {
            case self::VAT_METHOD_1_ALWAYS_21:
                $vatPercent = 21;
                break;
            case self::VAT_METHOD_3_AS_SPECIFIED_IN_RULES:
                $vatPercent = $this->_calculateVatAsSpecifiedInRules($document, $item, $isVatValid);
                break;
        }
        return $vatPercent;
    }

    protected function _oldVatPercent($document, $item, $vatMethod, $isVatValid) {
        $vatPercent = 0;
        switch ($vatMethod) {
            case self::VAT_METHOD_1_ALWAYS_21:
            case self::VAT_METHOD_3_AS_SPECIFIED_IN_RULES:
                $vatPercent = 21;
                break;
        }
        return $vatPercent;
    }

    protected $_customerVatNumbers = array();
    protected function _getCustomerVatNumber($customerId) {
        if ($customerId) {
            if (!isset($this->_customerVatNumbers[$customerId])) {
                $this->_customerVatNumbers[$customerId] = '';
                /* @var $invoices Mage_Sales_Model_Resource_Order_Invoice_Collection */
                $invoices = Mage::getResourceModel('sales/order_invoice_collection');
                $invoices->addAttributeToFilter('customer_id', $customerId);
                foreach ($invoices as $invoice) {
                    /* @var $invoice Mage_Sales_Model_Order_Invoice */
                    if ($result = $invoice->getBillingAddress()->getMVatNumber()) {
                        $this->_customerVatNumbers[$customerId] = $result;
                        break;
                    }
                }
            }
            return $this->_customerVatNumbers[$customerId];
        }
        return '';
    }

    /**
     * @param Mage_Sales_Model_Order $document
     * @param Mage_Sales_Model_Order_Item $item
     * @param $isVatValid
     * @return float
     */
    protected function _calculateVatAsSpecifiedInRules($document, $item, $isVatValid) {
        /* @var $calculator Mage_Tax_Model_Calculation */
        $calculator = Mage::getSingleton('tax/calculation');
        $address = $document->getBillingAddress();
        $store = $document->getStore();
        $product = Mage::getModel('catalog/product')->setStoreId($store->getId())->load($item->getProductId());

        /* @var $vatHelper Mana_Vat_Helper_Data */
        $vatHelper = Mage::helper('mana_vat');
        $customerClassId = $vatHelper->getCustomerClassId($isVatValid);

        $request = $calculator->getRateRequest($address, $address, $customerClassId, $store);
        $request->setProductClassId($product->getTaxClassId());

        return $calculator->getRate($request);
    }

    /**
     * @param Mage_Sales_Model_Order $document
     * @param Mage_Sales_Model_Order_Item $item
     * @param $vatMethod
     * @param $vatPercent
     * @param $isVatValid
     * @return void
     */
    protected function _recalculateItem($document, $item, $vatMethod, $vatPercent, $isVatValid) {
        $qty = $item->getQtyOrdered();

        if ($keepAmountsWithoutVat = ($vatMethod == self::VAT_METHOD_3_AS_SPECIFIED_IN_RULES && $isVatValid == Mana_Vat_Helper_Data::VALID) ? 1 : 0) {
            // taken values
            $price = $item->getPrice();


            // calculated values
            $item->setTaxPercent($vatPercent);
        }
        else {
            // taken values
            $amountWithTax = 0;

            // calculated values
            $amount = round($amountWithTax / (1 + ($vatPercent / 100)), 2);
            $taxAmount = $amountWithTax - $amount;
            $price = round($amount / $qty, 2);

            $item->setPrice($price)->setBasePrice($price)->setOriginalPrice($price)->setBaseOriginalPrice($price);
            $item->setTaxPercent($vatPercent);
        }
    }

    protected function _recalculateParentItem(Mage_Sales_Model_Quote_Item_Abstract $item) {
        $rowTaxAmount = 0;
        $baseRowTaxAmount = 0;
        foreach ($item->getChildren() as $child) {
            $rowTaxAmount += $child->getTaxAmount();
            $baseRowTaxAmount += $child->getBaseTaxAmount();
        }
        $item->setTaxAmount($rowTaxAmount);
        $item->setBaseTaxAmount($baseRowTaxAmount);
        return $this;
    }

    protected $_customers = array();

    /**
     * @param $customerId
     * @return Mage_Customer_Model_Customer
     */
    public function recalculateCustomer($customerId) {
        if ($customerId) {
            if (!isset($this->_customers[$customerId])) {
                /* @var $customer Mage_Customer_Model_Customer */
                $customer = Mage::getModel('customer/customer')->load($customerId);
                if (!$customer->getDefaultBillingAddress()) {
                    $address = null;

                    /* @var $invoices Mage_Sales_Model_Resource_Order_Invoice_Collection */
                    $invoices = Mage::getResourceModel('sales/order_invoice_collection');
                    $invoices->getSelect()
                        ->join(array('order' => 'sales_flat_order'), '`order`.entity_id=main_table.order_id', 'customer_id')
                        ->where('`order`.customer_id = ?', $customerId);
                    $invoices = $invoices->load()->getItems();
                    if (count($invoices)) {
                        $invoice = array_pop($invoices);
                        $address = Mage::getModel('customer/address');
                        Mage::helper('core')->copyFieldset('sales_convert_quote_address', 'to_customer_address',
                            $invoice->getBillingAddress(), $address);
                        $customer->addAddress($address);
                        $address->setIsDefaultBilling(true);
                        $address->setIsDefaultShipping(true);
                    }
                    else {
                        /* @var $orders Mage_Sales_Model_Resource_Order_Collection */
                        $orders = Mage::getResourceModel('sales/order_collection');
                        $orders->addAttributeToFilter('customer_id', $customerId);
                        $orders = $orders->load()->getItems();
                        if (count($orders)) {
                            $order = array_pop($orders);
                            $address = Mage::getModel('customer/address');
                            Mage::helper('core')->copyFieldset('sales_convert_quote_address', 'to_customer_address',
                                $order->getBillingAddress(), $address);
                            $customer->addAddress($address);
                            $address->setIsDefaultBilling(true);
                            $address->setIsDefaultShipping(true);
                        }
                    }
                    if ($address) {
                        if (!$address->getCountryId()) {
                            $address->setCountryId(Mage::helper('mana_geolocation')->find($customer->getEmail()));
                        }

                        $taxClassId = $customer->getTaxClassId();

                        /* @var $vatHelper Mana_Vat_Helper_Data */
                        $vatHelper = Mage::helper('mana_vat');
                        $isVatValid = $address->getMVatNumber()
                                ? $vatHelper->validateVat($address->getMVatNumber())
                                : Mana_Vat_Helper_Data::INVALID;


                        $correctTaxClassId = Mage::helper('mana_vat')->getCustomerClassId($isVatValid);
                        if ($taxClassId != $correctTaxClassId) {
                            $customerGroup = Mage::getModel('customer/group')->load($customer->getGroupId());
                            $taxIndependentGroupCode = $customerGroup->getTaxIndependentCode();
                            if ($correctCustomerGroupId = Mage::helper('local_manadev')->findCustomerGroupIdByTaxClassAndCode($correctTaxClassId, $taxIndependentGroupCode)) {
                                $customer->setGroupId($correctCustomerGroupId);
                            }
                        }

                        $customer->save();
                        $customer = Mage::getModel('customer/customer')->load($customerId);
                    }
                }

                $this->_customers[$customerId] = $customer;
            }
            return $this->_customers[$customerId];
        }
        else {
            return null;
        }
    }

    /**
     * @param Mage_Sales_Model_Order_Address $address
     * @param Mage_Customer_Model_Customer $customer
     * @return mixed
     */
    public function recalculateBillingAddress($address, $customer) {
        if (!$address->getCountryId()) {
            $address->setCountryId($customer->getDefaultBillingAddress()->getCountry());
        }
        return $address;
    }
    public function recalculateOrder($orderId) {
        /* @var $order Mage_Sales_Model_Order */
        $order = Mage::getModel('sales/order')->load($orderId);
        if (!$order->getMRecalculated()) {
            $this->recalculateDocument($order);
            $order->save();
        }
        return $order;
    }

    /**
     * @param Mage_Sales_Model_Order $document order, invoice or credit memo
     * @return Local_Manadev_Helper_Data
     */
    public function recalculateDocument(&$document) {
        if (!Mage::getStoreConfigFlag('local_manadev/accounting/recalculate')) {
            return $this;
        }
        try {
            /* @var $document Mage_Sales_Model_Order */
            $date = $this->getDocumentEffectiveDate($document);
            $vatMethod = $this->_vatMethod($date);

            if ($document instanceof Mage_Sales_Model_Order) {
                $order = $document;
            }
            else {
                $order = $this->recalculateOrder($document->getOrderId());
            }

            if (/*!($document instanceof Mage_Sales_Model_Order_Creditmemo) && */$vatMethod == self::VAT_METHOD_3_AS_SPECIFIED_IN_RULES && $customer = $this->recalculateCustomer($order->getCustomerId())) {
                $address = $this->recalculateBillingAddress($document->getBillingAddress(), $customer);

                /* @var $vatHelper Mana_Vat_Helper_Data */
                $vatHelper = Mage::helper('mana_vat');
                $isVatValid = $address->getMVatNumber()
                        ? $vatHelper->validateVat($address->getMVatNumber())
                        : Mana_Vat_Helper_Data::INVALID;


                /* @var $quote Mage_Sales_Model_Quote */
                $quote = Mage::getModel('sales/quote');
                $quote
                    ->setCustomer($customer)
                    ->setStoreId($document->getStore()->getId());

                $quote->getBillingAddress()->importOrderAddress($document->getBillingAddress());
                $calculationSettings = array();
                foreach ($document->getItemsCollection() as $item) {
                    if (is_null($item->getParentItem())) {
                        $product = Mage::getModel('catalog/product')
                                ->setStoreId($document->getStore()->getId())
                                ->load($item->getProductId());
                        if (!$product->getId()) {
                            continue;
                        }

                        $info = $item->getProductOptionByCode('info_buyRequest');
                        $info = new Varien_Object($info);

                        if ($document instanceof Mage_Sales_Model_Order) {
                            $qty = $item->getQtyOrdered();
                        }
                        else {
                            $qty = $item->getQty();
                        }
                        $info->setQty($qty);

                        $quoteItem = $quote->addProduct($product, $info);
                        if ($document instanceof Mage_Sales_Model_Order) {
                            $orderItem = $item;
                        }
                        else {
                            /* @var $item Mage_Sales_Model_Order_Invoice_Item */
                            $orderItem = $item->getOrderItem();
                        }
                        $discountPercent = $orderItem->getDiscountPercent(); // do not alter
                        if ($item->getDiscountAmount() > 0 && $discountPercent < 0.01) { // amount discount
                            $discountPercent = ($item->getDiscountAmount() / $item->getRowTotal()) * 100;
                        }
                        $oldTaxPercent = $this->_oldVatPercent($document, $item, $vatMethod, $isVatValid); // do alter
                        $taxPercent = $this->_vatPercent($document, $item, $vatMethod, $isVatValid); // do alter

                        /**
                         * The order is recalculated based in items price without VAT. Documents before recalculation
                         * always store Price with old VAT included. Which VAT? It is calculated in $oldTaxPercent.
                         *
                         * In case we want to preserve grand total (1), we need to preserve Price with VAT in each item. So
                         * we exclude _new_ VAT from original price.
                         *
                         * In case we want to preserve sub total (for business customers) (2), we need to preserve
                         * Price without VAT in each item. So we exclude _old_ VAT from original price.
                         *
                         */
                        if ($preserveGrandTotal = $item->getData('m_preserve_grand_total')) {
                            $preserveGrandTotal = $preserveGrandTotal == 1;
                            $price = $item->getData('m_base_price');
                        }
                        else {
                            $preserveGrandTotal = !(
                                    $vatMethod == self::VAT_METHOD_3_AS_SPECIFIED_IN_RULES &&
                                            $isVatValid == Mana_Vat_Helper_Data::VALID
                            ); // typically we want to, except for business customers who got paper invoices earlier

                            $priceWithVat = ($item->getRowTotal() - $item->getDiscountAmount() + $item->getTaxAmount()) / $qty;
                            $priceWithVat /= 1 - $discountPercent / 100;
                            if (($priceWithVat - $item->getPriceInclTax())  > 0.011 && trim($customer->getName()) != 'Vladislav Osmianskij') {
                                throw new Exception('Not implemented: unexpected price with VAT');
                            }
                            $priceWithVat = $item->getPriceInclTax();
                            if ($preserveGrandTotal) {
                                $price = round($priceWithVat / (1 + $taxPercent / 100), 4);
                            } else {
                                $price = round($priceWithVat / (1 + $oldTaxPercent / 100), 4);
                            }
                        }
                        $quoteItem
                            ->setOriginalCustomPrice($price)
                            ->setMOriginalDiscountPercent($discountPercent)
                            ->setMOriginalTaxPercent($taxPercent);

                       $calculationSettings[$item->getId()] = array(
                           'm_preserve_grand_total' => $preserveGrandTotal ? 1 : 2,
                           'm_base_price' => $price,
                           'm_old_vat_percent' => $oldTaxPercent,
                           'm_new_vat_percent' => $taxPercent,
                           'm_quote_item' => $quoteItem,
                           'm_document_item' => $item,
                       );
                    } else {
                        throw new Exception('Not implemented: unexpected grouped/configurable/bundle product');
                    }
                }

                $recalculate  = false;
                foreach ($calculationSettings as $calculationSetting) {
                    if ($calculationSetting['m_old_vat_percent'] > 0 || $calculationSetting['m_new_vat_percent'] > 0) {
                        $recalculate = true;
                    }
                }

                if ($recalculate) {
                    $orderInvoiced = false;
                    $orderRefunded = false;
                    $orderCanceled = false;
                    if ($document instanceof Mage_Sales_Model_Order) {
                        $orderInvoiced = count($document->getInvoiceCollection()) > 0;
                        $orderRefunded = $document->getState() == Mage_Sales_Model_Order::STATE_CLOSED;//count($document->getCreditmemosCollection()) > 0;
                        $orderCanceled = $document->getState() == Mage_Sales_Model_Order::STATE_CANCELED;
                    }
                    $quote->collectTotals();

                    foreach ($calculationSettings as $calculationSetting) {
                        $item = $calculationSetting['m_document_item'];
                        $quoteItem = $calculationSetting['m_quote_item'];
                        $item
                            ->setMPreserveGrandTotal($calculationSetting['m_preserve_grand_total'])
                            ->setMBasePrice($calculationSetting['m_base_price']);
                        $item
                            ->setOriginalPrice($quoteItem->getPrice())
                            ->setPrice($quoteItem->getPrice())
                            ->setBaseOriginalPrice($quoteItem->getPrice())
                            ->setBasePrice($quoteItem->getPrice());
                        $item
                            ->setTaxPercent($quoteItem->getTaxPercent())
                            ->setTaxAmount($quoteItem->getTaxAmount())
                            ->setBaseTaxAmount($quoteItem->getTaxAmount());
                        $item
                            ->setDiscountPercent($quoteItem->getDiscountPercent())
                            ->setDiscountAmount($quoteItem->getDiscountAmount())
                            ->setBaseDiscountAmount($quoteItem->getDiscountAmount());
                        $item
                            ->setRowTotal($quoteItem->getRowTotal())
                            ->setBaseRowTotal($quoteItem->getRowTotal());
                        $item
                            ->setPriceInclTax($quoteItem->getPriceInclTax())
                            ->setBasePriceInclTax($quoteItem->getPriceInclTax())
                            ->setRowTotalInclTax($quoteItem->getRowTotalInclTax())
                            ->setBaseRowTotalInclTax($quoteItem->getRowTotalInclTax());
                        if ($orderInvoiced) {
                            $item
                                ->setTaxInvoiced($quoteItem->getTaxAmount())
                                ->setBaseTaxInvoiced($quoteItem->getTaxAmount());
                            $item
                                ->setDiscountInvoiced($quoteItem->getDiscountAmount())
                                ->setBaseDiscountInvoiced($quoteItem->getDiscountAmount());
                            $item
                                ->setRowInvoiced($quoteItem->getRowTotal())
                                ->setBaseRowInvoiced($quoteItem->getRowTotal());
                        }
                        if ($orderRefunded) {
                            $item
                                ->setAmountRefunded($quoteItem->getRowTotal())
                                ->setBaseAmountRefunded($quoteItem->getRowTotal());
                            $item
                                ->setTaxRefunded($quoteItem->getTaxAmount());
                        }
                        if ($orderCanceled) {
                            $item
                                ->setTaxCanceled($quoteItem->getTaxAmount());
                        }
                    }
                    $document
                        ->setBaseDiscountAmount(-($quote->getSubtotal() - $quote->getSubtotalWithDiscount()))
                        ->setDiscountAmount(-($quote->getSubtotal() - $quote->getSubtotalWithDiscount()));
                    $document
                        ->setBaseGrandTotal($quote->getGrandTotal())
                        ->setGrandTotal($quote->getGrandTotal())
                        ->setBaseSubtotalInclTax($quote->getGrandTotal())
                        ->setSubtotalInclTax($quote->getGrandTotal());
                    $document
                        ->setBaseSubtotal($quote->getSubtotal())
                        ->setSubtotal($quote->getSubtotal());
                    $document
                        ->setBaseTaxAmount($quote->getGrandTotal() - $quote->getSubtotalWithDiscount())
                        ->setTaxAmount($quote->getGrandTotal() - $quote->getSubtotalWithDiscount());
                    $document->setCustomerGroupId($customer->getGroupId());
                    if ($orderInvoiced) {
                        $document
                            ->setBaseDiscountInvoiced($quote->getSubtotal() - $quote->getSubtotalWithDiscount())
                            ->setDiscountInvoiced($quote->getSubtotal() - $quote->getSubtotalWithDiscount());
                        $document
                            ->setBaseSubtotalInvoiced($quote->getSubtotal())
                            ->setSubtotalInvoiced($quote->getSubtotal());
                        $document
                            ->setBaseTaxInvoiced($quote->getGrandTotal() - $quote->getSubtotalWithDiscount())
                            ->setTaxInvoiced($quote->getGrandTotal() - $quote->getSubtotalWithDiscount());
                        $document
                            ->setBaseTotalInvoiced($quote->getGrandTotal())
                            ->setTotalInvoiced($quote->getGrandTotal());
                    }
                    if ($orderRefunded) {
                        $document
                            ->setBaseDiscountRefunded($quote->getSubtotal() - $quote->getSubtotalWithDiscount())
                            ->setDiscountRefunded($quote->getSubtotal() - $quote->getSubtotalWithDiscount());
                        $document
                            ->setBaseSubtotalRefunded($quote->getSubtotal())
                            ->setSubtotalRefunded($quote->getSubtotal());
                        $document
                            ->setBaseTaxRefunded($quote->getGrandTotal() - $quote->getSubtotalWithDiscount())
                            ->setTaxRefunded($quote->getGrandTotal() - $quote->getSubtotalWithDiscount());
                        $offlinePart = $document->getBaseTotalOfflineRefunded() / ($document->getBaseTotalOfflineRefunded() + $document->getBaseTotalOnlineRefunded());
                        $onlinePart = $document->getBaseTotalOnlineRefunded() / ($document->getBaseTotalOfflineRefunded() + $document->getBaseTotalOnlineRefunded());
                        $document
                            ->setBaseTotalOfflineRefunded(round($quote->getGrandTotal() * $offlinePart, 2))
                            ->setBaseTotalOnlineRefunded(round($quote->getGrandTotal() * $onlinePart, 2))
                            ->setTotalOfflineRefunded(round($quote->getGrandTotal() * $offlinePart, 2))
                            ->setTotalOnlineRefunded(round($quote->getGrandTotal() * $onlinePart, 2))
                            ->setBaseTotalRefunded($quote->getGrandTotal())
                            ->setTotalRefunded($quote->getGrandTotal());
                    }
                    if ($orderCanceled) {
                        $document
                            ->setBaseDiscountCanceled($quote->getSubtotal() - $quote->getSubtotalWithDiscount())
                            ->setDiscountCanceled($quote->getSubtotal() - $quote->getSubtotalWithDiscount());
                        $document
                            ->setBaseSubtotalCanceled($quote->getSubtotal())
                            ->setSubtotalCanceled($quote->getSubtotal());
                        $document
                            ->setBaseTaxCanceled($quote->getGrandTotal() - $quote->getSubtotalWithDiscount())
                            ->setTaxCanceled($quote->getGrandTotal() - $quote->getSubtotalWithDiscount());
                        $document
                            ->setBaseTotalCanceled($quote->getGrandTotal())
                            ->setTotalCanceled($quote->getGrandTotal());
                    }
                    $document->setMRecalculated(1);
                }
            }
        } catch (Exception $e) {
            Mage::log((string)$e, Zend_Log::CRIT, 'fix_vat.log');
            throw $e;
        }
        return $this;
    }

    public function prepareDocumentForAccounting(&$document) {
	    if (!Mage::getStoreConfigFlag('local_manadev/accounting/convert')) {
	        return $this;
	    }
	    try {
            // parameter wrapper
            if (is_array($document)) {
                $data = &$document;
            }
            else {
                $data = $document->getData();
            }

            $date = $this->getDocumentEffectiveDate($data);

            $data['m_date'] = $date['date'];
            $data['m_timezone'] = $date['timezone'];

            /* @var $address Mage_Sales_Model_Order_Address */
            $address = Mage::getModel('sales/order_address')->load($data['billing_address_id']);
            /* @var $country Mage_Directory_Model_Country */
            $country = Mage::getModel('directory/country')->load($address->getCountry());
            $data['m_country'] = $country->getName();
            $data['m_is_business'] = $address->getCompany();
            /* @var $order Mage_Sales_Model_Order */
            $order = Mage::getModel('sales/order')->load($data['order_id']);
            $data['m_sales_account'] = Mage::getStoreConfig('local_manadev/accounting/service_account', $order->getStore());
            foreach ($order->getItemsCollection() as $item) {
                /* @var $item Mage_Sales_Model_Order_Item */
                if (is_null($item->getParentItem())) {
                    /* @var $product Mage_Catalog_Model_Product */
                    $product = Mage::getModel('catalog/product')
                            ->setStoreId($order->getStore()->getId())
                            ->load($item->getProductId());

                    if (!$product->getIsService()) {
                        $data['m_sales_account'] = Mage::getStoreConfig('local_manadev/accounting/product_account', $order->getStore());
                        break;
                    }
                }
            }

            $data['m_usd_total'] = $data['grand_total'] - $data['tax_amount'];
            $data['m_vat_percent'] = round(($data['grand_total'] / $data['m_usd_total'] - 1)* 100);
            $data['m_usd_vat'] = $data['tax_amount'];

            // and if it is not future date, calculate amounts in accounting currency
            $tomorrowDate = Mage::app()->getLocale()->date(null, Varien_Date::DATETIME_INTERNAL_FORMAT)->addDay(1)->toString('Y-MM-dd');
            if ($data['m_date'] < $tomorrowDate) {
                $data['m_exchange_rate'] = $this->_getRate($data['order_currency_code'], $data['m_date']);
                $data['m_total'] = round($data['grand_total'] * $data['m_exchange_rate'] / (1 + ($data['m_vat_percent'] / 100)), 2);
                $data['m_vat'] = round($data['m_total'] * ($data['m_vat_percent'] / 100), 2);
                $data['m_grand_total'] = $data['m_total'] + $data['m_vat'];
            }
            else {
                $data['m_exchange_rate'] = null;
                $data['m_total'] = null;
                $data['m_vat'] = null;
                $data['m_grand_total'] = null;
            }

            // result wrapper
            if (!is_array($document)) {
                $document->setData($data);
            }
        }
        catch (Exception $e) {
            Mage::log((string)$e, Zend_Log::CRIT, 'lietuva.log');
        }
		return $this;

	}

    public function _oldPrepareDocumentForAccounting(&$document) {
        if (!Mage::getStoreConfigFlag('local_manadev/accounting/convert')) {
            return $this;
        }
        try {
            // parameter wrapper
            if (is_array($document)) {
                $data = &$document;
            } else {
                $data = $document->getData();
            }

            // time zone setup history
            $createdAt = isset($data['created_at']) ? $data['created_at'] : Mage::app()->getLocale()->date(null, Varien_Date::DATETIME_INTERNAL_FORMAT)->toString('Y-MM-dd');
            $date = Mage::app()->getLocale()->date($createdAt, Varien_Date::DATETIME_INTERNAL_FORMAT, null, false);
            $visibleDate = Mage::app()->getLocale()->date($createdAt, Varien_Date::DATETIME_INTERNAL_FORMAT)->toString('Y-MM-dd');
            if ($visibleDate > '2011-08-31') {
                $timezone = 'Europe/Minsk';
            } elseif ($visibleDate > '2011-07-31') {
                $timezone = 'America/Los_Angeles';
            }
            else {
                $timezone = '';
            }
            if ($timezone) {
                $date->setTimezone($timezone);
            }

            // special case - during transition we made accounting easier by moving all documents to last working day
            if ($date->toString('Y-MM-dd') > '2011-08-31' && $date->toString('Y-MM-dd') < '2011-09-21') {
                $date->set('2011-09-20');
            }

            $data['m_date'] = $date->toString('Y-MM-dd');
            $data['m_timezone'] = $timezone;

            // VAT setup history
            if ($data['m_date'] > '2012-05-01') {
                $data['m_vat_percent'] = 21;
            } elseif ($data['m_date'] > '2011-09-14') {
                $data['m_vat_percent'] = 0;
            }
            else {
                $data['m_vat_percent'] = 21;
            }

            // now, missing amounts in original currency (USD)
            $data['m_usd_total'] = round($data['grand_total'] / (1 + ($data['m_vat_percent'] / 100)), 2);
            $data['m_usd_vat'] = $data['grand_total'] - $data['m_usd_total'];

            // and if it is not future date, calculate amounts in accounting currency
            $tomorrowDate = Mage::app()->getLocale()->date(null, Varien_Date::DATETIME_INTERNAL_FORMAT)->addDay(1)->toString('Y-MM-dd');
            if ($data['m_date'] < $tomorrowDate) {
                $data['m_exchange_rate'] = $this->_getRate($data['order_currency_code'], $data['m_date']);
                $data['m_total'] = round($data['grand_total'] * $data['m_exchange_rate'] / (1 + ($data['m_vat_percent'] / 100)), 2);
                $data['m_vat'] = round($data['m_total'] * ($data['m_vat_percent'] / 100), 2);
                $data['m_grand_total'] = $data['m_total'] + $data['m_vat'];
            } else {
                $data['m_exchange_rate'] = null;
                $data['m_total'] = null;
                $data['m_vat'] = null;
                $data['m_grand_total'] = null;
            }

            // result wrapper
            if (!is_array($document)) {
                $document->setData($data);
            }
        } catch (Exception $e) {
            Mage::log((string)$e, Zend_Log::CRIT, 'lietuva.log');
        }
        return $this;

    }

    protected function _getRate($currencyCode, $date) {
        if ($date >= '2015-01-01') {
            // Lithuania adopted EURO, so no conversion takes place
            return 1;
        }
        else {
            return Mage::helper('mana_lt')->getRate($currencyCode, $date);
        }
	}
	public function getCustomerAddress() {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            /* @var $customer Mage_Customer_Model_Customer */
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            if ($customer->getDefaultBillingAddress() && ($addressId = $customer->getDefaultBillingAddress()->getId())) {
                return $customer->getDefaultBillingAddress();
            }
        }
        return null;
    }

    public function findCustomerGroupIdByTaxClassAndCode($taxClassId, $code) {
        /* @var $res Mage_Core_Model_Resource */
        $res = Mage::getSingleton(strtolower('Core/Resource'));
        $db = $res->getConnection('read');
        return $db->fetchOne($db->select()
                    ->from($res->getTableName('customer_group'), 'customer_group_id')
                    ->where('tax_class_id = ?', $taxClassId)
                    ->where('tax_independent_code = ?', $code)
        );
    }

    public function getDocumentEffectiveDate($document) {
        if (is_array($document)) {
            $data = &$document;
        } else {
            $data = $document->getData();
        }

        $createdAt = isset($data['created_at']) ? $data['created_at'] : Mage::app()->getLocale()->date(null, Varien_Date::DATETIME_INTERNAL_FORMAT)->toString('Y-MM-dd');
        $date = Mage::app()->getLocale()->date($createdAt, Varien_Date::DATETIME_INTERNAL_FORMAT, null, false);
        $visibleDate = Mage::app()->getLocale()->date($createdAt, Varien_Date::DATETIME_INTERNAL_FORMAT)->toString('y-MM-dd');
        if ($visibleDate > '2011-08-31') {
            $timezone = 'Europe/Minsk';
        } elseif ($visibleDate > '2011-07-31') {
            $timezone = 'America/Los_Angeles';
        }
        else {
            $timezone = '';
        }
        if ($timezone) {
            $date->setTimezone($timezone);
        }

        // special case - during transition we made accounting easier by moving all documents to last working day
        if (is_array($document) || !($document instanceof Mage_Sales_Model_Order)) {
            if ($date->toString('y-MM-dd') > '2011-08-31' && $date->toString('y-MM-dd') < '2011-09-21') {
                $date->set('2011-09-20');
            }
        }

        return array(
            'date' => $date->toString('y-MM-dd'),
            'timezone' => $timezone,
        );
    }

    public function createNewZipFileWithLicense($linkPurchasedItem) {
        $licenseVerificationNo = $linkPurchasedItem->getData('m_license_verification_no');

        /* @var $storage Mage_Downloadable_Helper_File */
        $storage = Mage::helper('downloadable/file');
        /** @var Mage_Downloadable_Model_Link $linkModel */
        $linkModel = Mage::getModel('downloadable/link')->load($linkPurchasedItem->getLinkId());
        $productModel = Mage::getModel('catalog/product')->load($linkPurchasedItem->getProductId());
        $resource = $storage->getFilePath(Mage_Downloadable_Model_Link::getBasePath(), $linkModel->getLinkFile());

        $pathinfo = pathinfo($resource);

        $newZipFilename = $pathinfo['dirname'] . DS . $pathinfo['filename'] . "-" . $licenseVerificationNo . "." . $pathinfo['extension'];

        if(!file_exists($newZipFilename)) {
            copy($resource, $newZipFilename);
            $zip = new ZipArchive();
            if ($zip->open($newZipFilename) === true) {

                if($productModel->getData('platform') == Local_Manadev_Model_Platform::VALUE_MAGENTO_2) {
                    $moduleDir = "app/code/Manadev/Core/";
                } else {
                    $moduleDir = "app/code/local/Mana/Core/";
                }
                $licenseDir = $moduleDir . "license";
                $pubKeyDir = $moduleDir . "key/public";
                $privateKeyDir = $moduleDir . "key/private";
                $zip->addEmptyDir($licenseDir);
                $zip->addEmptyDir($pubKeyDir);
                $zip->addEmptyDir($privateKeyDir);

                $sku = $productModel->getData('sku');
                $version = $this->_getLocalKeyModel()->getVersionFromZipFile($linkModel->getLinkFile());

                $zip->addFromString("{$licenseDir}/{$licenseVerificationNo}", "{$sku} --- {$version}");

                $keys = $this->generateKeys();
                $keyName = uniqid() . ".pem";
                $zip->addFromString("{$pubKeyDir}/{$keyName}", $keys['public']);
                $zip->addFromString("{$privateKeyDir}/{$keyName}", $keys['private']);
                $availableKeysDir = Mage::getBaseDir() . DS . 'available_keys';
                $localPubKeyDir = $availableKeysDir . DS . 'public' . DS;
                $localPrivateKeyDir = $availableKeysDir . DS . 'private' . DS;
                if(!file_exists($availableKeysDir)) {
                    mkdir($availableKeysDir);
                }
                if (!file_exists($localPubKeyDir)) {
                    mkdir($localPubKeyDir, null, true);
                }
                if (!file_exists($localPrivateKeyDir)) {
                    mkdir($localPrivateKeyDir, null, true);
                }

                $localPubKey = $localPubKeyDir . $keyName;
                $localPrivateKey = $localPrivateKeyDir . $keyName;
                file_put_contents($localPubKey, $keys['public']);
                file_put_contents($localPrivateKey, $keys['private']);
                $linkPurchasedItem->setData('m_key', $keyName);

                $zip->close();
            }

            $linkPurchasedItem->setData('link_file', str_replace(Mage_Downloadable_Model_Link::getBasePath(), "", $newZipFilename));
        }
    }

    public function generateKeys() {
        // TODO: Find a way to set config file outside code
        $config = array(
            "digest_alg" => "sha512",
            "private_key_bits" => 4096,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
            'config' => 'C:/wamp/bin/php/php5.5.12/extras/ssl/openssl.cnf',
        );

        $res = openssl_pkey_new($config);

        if ($res === false) {
            $err = openssl_error_string();
        }
        // Extract the private key from $res to $privateKey
        openssl_pkey_export($res, $privateKey, null, $config);

        // Extract the public key from $res to $pubKey
        $pubKey = openssl_pkey_get_details($res);
        $pubKey = $pubKey["key"];

        return array(
            'public' => $pubKey,
            'private' => $privateKey
        );
    }

    /**
     * @return Local_Manadev_Model_Key
     */
    protected function _getLocalKeyModel() {
        return Mage::getModel('local_manadev/key');
    }
}