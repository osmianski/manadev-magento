<?php
/** 
 * @category    Mana
 * @package     Mana_Seo
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 * @method string getStatus()
 * @method Mana_Seo_Model_ParsedUrl setStatus(string $value)
 * @method string getPageUrlKey()
 * @method Mana_Seo_Model_ParsedUrl setPageUrlKey(string $value)
 * @method string getRoute()
 * @method Mana_Seo_Model_ParsedUrl setRoute(string $value)
 * @method string getSuffix()
 * @method Mana_Seo_Model_ParsedUrl setSuffix(string $value)
 * @method string getPath()
 * @method Mana_Seo_Model_ParsedUrl setPath(string $value)
 */
class Mana_Seo_Model_ParsedUrl extends Varien_Object {
    protected $_parameters = array();

    /**
     * @param string $key
     * @param mixed $value
     * @return Mana_Seo_Model_ParsedUrl
     */
    public function addParameter($key, $value) {
        if (!isset($this->_parameters[$key])) {
            $this->_parameters[$key] = array();
        }
        $this->_parameters[$key][] = $value;
        return $this;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasParameter($key) {
        return isset($this->_parameters[$key]);
    }

    public function getParameter($key) {
        return $this->_parameters[$key];
    }
}