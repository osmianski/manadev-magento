<?php
/** 
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 * @method Mana_Core_Helper_Logger beginSeoMatch(string $message = null)
 * @method Mana_Core_Helper_Logger logSeoMatch(string $message = null)
 * @method Mana_Core_Helper_Logger endSeoMatch(string $message = null)

 * @method Mana_Core_Helper_Logger beginSeoUrl(string $message = null)
 * @method Mana_Core_Helper_Logger logSeoUrl(string $message = null)
 * @method Mana_Core_Helper_Logger endSeoUrl(string $message = null)

 * @method Mana_Core_Helper_Logger beginDbIndexerFailure(string $message = null)
 * @method Mana_Core_Helper_Logger logDbIndexerFailure(string $message = null)
 * @method Mana_Core_Helper_Logger endDbIndexerFailure(string $message = null)

 * @method Mana_Core_Helper_Logger beginUrlIndexer(string $message = null)
 * @method Mana_Core_Helper_Logger logUrlIndexer(string $message = null)
 * @method Mana_Core_Helper_Logger endUrlIndexer(string $message = null)

 * @method Mana_Core_Helper_Logger beginTemp(string $message = null)
 * @method Mana_Core_Helper_Logger logTemp(string $message = null)
 * @method Mana_Core_Helper_Logger endTemp(string $message = null)
 */
class Mana_Core_Helper_Logger extends Mage_Core_Helper_Abstract {
    const INDENT_WIDTH = 4;
    protected $_isEnabled = array(
        'all' => false,
        'seo_match' => false,
        'seo_url' => false,
        'db_indexer_failure' => false,
        'url_indexer' => false,
        'temp' => true,
    );
    protected $_indent = array('all' => 0);

    public function __call($method, $args) {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper('mana_core');
        if ($core->startsWith($method, 'log')) {
            $key = $this->_underscore(substr($method, 3));
            if ($this->_isEnabled[$key]) {
                if (!isset($this->_indent[$key])) {
                    $this->_indent[$key] = 0;
                }
                if (!empty($args[0])) {
                    Mage::log(str_repeat(' ', $this->_indent[$key]) . $args[0], Zend_Log::DEBUG, "m_$key.log");
                }
                if ($key != 'all') {
                    if (!empty($args[0])) {
                        Mage::log(str_repeat(' ', $this->_indent['all']) . $args[0], Zend_Log::DEBUG, "m_all.log");
                    }
                }
            }

            return $this;
        }
        elseif ($core->startsWith($method, 'begin')) {
            $key = $this->_underscore(substr($method, 5));
            if ($this->_isEnabled[$key]) {
                if (!isset($this->_indent[$key])) {
                    $this->_indent[$key] = 0;
                }
                if (!empty($args[0])) {
                    Mage::log(str_repeat(' ', $this->_indent[$key]). $args[0], Zend_Log::DEBUG, "m_$key.log");
                }
                $this->_indent[$key] += self::INDENT_WIDTH;
                if ($key != 'all') {
                    if (!empty($args[0])) {
                        Mage::log(str_repeat(' ', $this->_indent['all']) . $args[0], Zend_Log::DEBUG, "m_all.log");
                    }
                    $this->_indent['all'] += self::INDENT_WIDTH;
                }
            }

            return $this;
        }
        elseif ($core->startsWith($method, 'end')) {
            $key = $this->_underscore(substr($method, 3));
            if ($this->_isEnabled[$key]) {
                if (!isset($this->_indent[$key])) {
                    $this->_indent[$key] = 0;
                }
                $this->_indent[$key] -= self::INDENT_WIDTH;
                if (!empty($args[0])) {
                    Mage::log(str_repeat(' ', $this->_indent[$key]) . $args[0], Zend_Log::DEBUG, "m_$key.log");
                }
                if ($key != 'all') {
                    $this->_indent['all'] -= self::INDENT_WIDTH;
                    if (!empty($args[0])) {
                        Mage::log(str_repeat(' ', $this->_indent['all']) . $args[0], Zend_Log::DEBUG, "m_all.log");
                    }
                }
            }

            return $this;
        }

        throw new Exception("Invalid method " . get_class($this) . "::" . $method . "(" . print_r($args, 1) . ")");
    }

    protected function _underscore($name) {
        return strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $name));
    }
}