<?php
/**
 * @category    Mana
 * @package     Mana_Vat
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * Generic helper functions for Mana_Vat module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class Mana_Vat_Helper_Data extends Mage_Core_Helper_Abstract {
    const INVALID = 0;
    const VALID = 1;
    const NON_EU = 2;

    const REQUEST_INTERVAL = 2; // seconds

    static $_prefixAliases = array(
        //'AT' => 'ATU'
    );
    static $_validVats = array('DE813581361', 'DE246185531', 'NL851007181B01', 'ATU48928406', 'DE 246185531', 'CZ28723881');
    protected $_lastFetched;
    protected $_debug = true;

    protected function _replaceAliases($vat) {
        /* @var $core Mana_Core_Helper_Data */ $core = Mage::helper(strtolower('Mana_Core'));

        foreach (self::$_prefixAliases as $alias => $code) {
            if ($core->startsWith($vat, $alias)) {
                $count = 1;
                $vat = str_replace($alias, $code, $vat, $count);
            }
        }
        return $vat;
    }
    protected function _isEu($country) {
        /* @var $res Mage_Core_Model_Resource */ $res = Mage::getSingleton(strtolower('Core/Resource'));
        $db = $res->getConnection('read');
        return $db->fetchOne($db->select()
            ->from($res->getTableName('tax_calculation_rate'), 'tax_country_id')
            ->where('rate = 21')
        ) ? true : false;

    }

    public function validateVat($vat) {
        if (in_array($vat, self::$_validVats)) {
            return self::VALID;
        }
        $cacheKey = 'VAT_VALID_'.$vat;
        if (!($result = Mage::app()->loadCache($cacheKey))) {
            $time = time();
            if ($this->_lastFetched && self::REQUEST_INTERVAL - $time - $this->_lastFetched > 0 ) {
                sleep(self::REQUEST_INTERVAL - $time - $this->_lastFetched);
            }
            $this->_lastFetched = $time;
            $result = $this->_validateVat($vat);
            Mage::app()->saveCache($result, $cacheKey, array('COLLECTION_DATA'), 60*60*24);
        }

        return $result;
    }
    protected function _validateVat($vat) {
        $this->_log('Fetching %s ...', $vat);
        try {
            $vat = $this->_replaceAliases($vat);
            $vat = preg_replace('/[ \.]/', '', $vat);
            if (!preg_match('/[A-Za-z]{2}\d+/', $vat)) {
                $this->_log('INVALID FORMAT');
                return self::INVALID;
            }
            if (strlen($vat) > 2 && $this->_isEu(substr($vat, 0, 2))) {
                ini_set('soap.wsdl_cache_enabled', '0');
                ini_set('soap.wsdl_cache_ttl', '0');
                $remote = new SoapClient('http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl', array('trace' => 1));
                $functions = $remote->__getFunctions();
                $result = $remote->checkVat(array('countryCode' => substr($vat, 0, 2), 'vatNumber' => substr($vat, 2)));

                if ($result->valid) {
                    $this->_log('VALID');
                    return self::VALID;
                } else {
                    $this->_log('INVALID');
                    return self::INVALID;
                }
            } else {
                $this->_log('NON_EU');
                return self::NON_EU;
            }
        }
        catch (Exception $e) {
            $this->_log("%s\n%s", $e->getMessage(), $e->getTraceAsString());
            if ($e->getMessage() == 'INVALID_INPUT') {
                return self::INVALID;
            }
            else {
                throw $e;
            }
        }
    }
    public function getCustomerClassId($isValid) {
        /* @var $res Mage_Core_Model_Resource */
        $res = Mage::getSingleton(strtolower('Core/Resource'));
        $db = $res->getConnection('read');
        return $db->fetchOne($db->select()
            ->from($res->getTableName('tax_class'), 'class_id')
            ->where('class_type = ?', 'CUSTOMER')
            ->where('auto_assign_condition = ?', '{{is_vat_valid}} = '.($isValid == self::VALID ? '1' : '0'))
        );
    }
    protected function _log($message) {
        if ($this->_debug) {
            $args = func_get_args();
            $message = call_user_func_array(array($this, '__'), $args);
            Mage::log($message, Zend_Log::DEBUG, 'mana_vat.log');
        }
        return $this;
    }
}