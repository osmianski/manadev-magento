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
class Mana_Core_Helper_Mbstring extends Mage_Core_Helper_Abstract {
    protected $_multiByte;
    public function __construct() {
        $this->_multiByte = function_exists('mb_strpos');
    }
    public function strpos($haystack, $needle, $offset = 0) {
        if ($this->_multiByte) {
            return mb_strpos($haystack, $needle, $offset);
        }
        else {
            return strpos($haystack, $needle, $offset);
        }
    }

    public function strrpos($haystack, $needle, $offset = null) {
        if ($this->_multiByte) {
            return is_null($offset) ? mb_strrpos($haystack, $needle) : mb_strrpos($haystack, $needle, $offset);
        }
        else {
            return is_null($offset) ? strrpos($haystack, $needle) : strrpos($haystack, $needle, $offset);
        }
    }

    public function strlen($haystack) {
        if ($this->_multiByte) {
            return mb_strlen($haystack);
        }
        else {
            return strlen($haystack);
        }
    }

    public function substr($string, $start, $length = null) {
        if ($this->_multiByte) {
            return is_null($length) ? mb_substr($string, $start) : mb_substr($string, $start, $length);
        }
        else {
            return is_null($length) ? substr($string, $start) : substr($string, $start, $length);
        }
    }

    public function endsWith($haystack, $needle) {
        return ($this->strrpos($haystack, $needle) === $this->strlen($haystack) - $this->strlen($needle));
    }

    public function startsWith($haystack, $needle) {
        return ($this->strpos($haystack, $needle) === 0);
    }

    public function stripos($haystack, $needle, $offset = 0) {
        if ($this->_multiByte) {
            return mb_stripos($haystack, $needle, $offset);
        }
        else {
            return stripos($haystack, $needle, $offset);
        }
    }
}