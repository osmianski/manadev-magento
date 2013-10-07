<?php
/**
 * @category    Mana
 * @package     Mana_Lt
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * Generic helper functions for Mana_Lt module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class Mana_Lt_Helper_Data extends Mage_Core_Helper_Abstract {
    public function getRate($currencyCode, $date) {
        /* @var $collection Mana_Lt_Resource_Rate_Collection */
        $collection = Mage::getResourceModel('mana_lt/rate_collection');
        $collection
            ->addFieldToFilter('currency_code', $currencyCode)
            ->addFieldToFilter('date', $date);
        foreach ($collection as $rate) {
            return $rate->getRate();
        }
        $rate = $this->_getLBRate($currencyCode, $date);
        Mage::getModel('mana_lt/rate')
            ->setCurrencyCode($currencyCode)
            ->setDate($date)
            ->setRate($rate)
            ->save();

        return $rate;
    }
    protected function _getLBRate($currencyCode, $date) {
        $url = "http://webservices.lb.lt/ExchangeRates/ExchangeRates.asmx/getExchangeRate?Currency=$currencyCode&Date=$date";
        Mage::log("Request: $url", Zend_Log::DEBUG, 'lietuva.log');
        $contents = file_get_contents($url);
        Mage::log("Response: $contents", Zend_Log::DEBUG, 'lietuva.log');
        $xml = simplexml_load_string($contents);
        Mage::log("XML: " . (string)$xml, Zend_Log::DEBUG, 'lietuva.log');
        if ($xml) {
            return (float)(string)$xml;
        } else {
            throw new Exception('Lietuvos Bankas neatsako!');
        }
    }
}