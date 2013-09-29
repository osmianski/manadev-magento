<?php
/** 
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Core_Helper_Seo extends Mage_Core_Helper_Abstract {
    protected $_urlSymbols;

    protected function _initUrlSymbols() {
        if (!$this->_urlSymbols) {
            $this->_urlSymbols = array();
            $this->_urlSymbols['-'] = Mage::getStoreConfig('mana/seo_symbols/dash');
            $this->_urlSymbols['/'] = Mage::getStoreConfig('mana/seo_symbols/slash');
            $this->_urlSymbols['+'] = Mage::getStoreConfig('mana/seo_symbols/plus');
            $this->_urlSymbols['_'] = Mage::getStoreConfig('mana/seo_symbols/underscore');
            $this->_urlSymbols["'"] = Mage::getStoreConfig('mana/seo_symbols/quote');
            $this->_urlSymbols['"'] = Mage::getStoreConfig('mana/seo_symbols/double_quote');
            $this->_urlSymbols['%'] = Mage::getStoreConfig('mana/seo_symbols/percent');
            $this->_urlSymbols['#'] = Mage::getStoreConfig('mana/seo_symbols/hash');
            $this->_urlSymbols['&'] = Mage::getStoreConfig('mana/seo_symbols/ampersand');
            $this->_urlSymbols[' '] = Mage::getStoreConfig('mana/seo_symbols/space');
        }

        return $this;
    }

    public function encode($text) {
        $this->_initUrlSymbols();
        foreach ($this->_urlSymbols as $symbol => $urlSymbol) {
            $text = str_replace($symbol, $urlSymbol, $text);
        }

        return $text;
    }

    public function decode($text) {
        $this->_initUrlSymbols();
        $result = '';
        for ($i = 0; $i < mb_strlen($text);) {
            $found = false;
            foreach ($this->_urlSymbols as $symbol => $urlSymbol) {
                if (mb_strpos($text, $urlSymbol, $i) === $i) {
                    $result .= $symbol;
                    $i += mb_strlen($urlSymbol);
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $result .= mb_substr($text, $i++, 1);
            }
        }

        return $result;
    }

    public function select($expr) {
        $this->_initUrlSymbols();
        foreach ($this->_urlSymbols as $symbol => $urlSymbol) {
            $escapedSymbol = str_replace("'", "\\'", $symbol);
            $expr = "REPLACE($expr, '$escapedSymbol', '$urlSymbol')";
        }

        return $expr;
    }
}