<?php
/** 
 * @category    Mana
 * @package     Mana_Seo
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
abstract class Mana_Seo_Helper_PageType extends Mage_Core_Helper_Abstract {
    protected $_code;

    abstract public function getCurrentSuffix();
    abstract public function getSuffixHistoryType();
    abstract public function setPage($token);
    abstract public function matchRoute($route);
    abstract public function getUrlKey($urlModel);
    public function setCode($code) {
        $this->_code = $code;
        return $this;
    }
    public function getCode() {
        return $this->_code;
    }
}