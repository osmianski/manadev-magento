<?php
/** 
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
abstract class Mana_Core_Helper_PageType extends Mage_Core_Helper_Abstract {
    protected $_code;

    abstract public function getRoutePath();
    abstract public function getCurrentSuffix();
    public function setCode($code) {
        $this->_code = $code;
        return $this;
    }
    public function getCode() {
        return $this->_code;
    }

    public function matchRoute($route) {
        return $route == $this->getRoutePath();
    }
}