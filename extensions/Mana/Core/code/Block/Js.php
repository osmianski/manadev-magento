<?php
/**
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * All visual blocks on page use this block to provide initial data and translations to client scripts
 * @author Mana Team
 *
 */
class Mana_Core_Block_Js extends Mage_Core_Block_Template {
    protected $_config;

    protected function _construct() {
        $this->_config = array(
            'debug' => Mage::app()->getStore()->isAdmin() || Mage::getStoreConfigFlag('mana/ajax/debug'),
        );
    }

    public function setConfig($key, $value) {
        $this->_config[$key] = $value;
        return $this;
    }
    public function getConfig() {
        return $this->_config;
    }
	/**
	 * Contains all the translations registered to be passed to client-side scripts
	 * @var array | null
	 */
	protected $_translations;
	/**
	 * Contains all key-value pair arrays registered to be passed to client-side scripts
	 * @var array | null
	 */
	protected $_options;
	
	/**
	 * Makes translations of specified strings to be available in client-side scripts.
	 * @param array $translations
	 * @return Mana_Core_Helper_Js
	 */
	public function translations($translations) {
		foreach ($translations as $key) {
			$value = $this->__($key);
			//if ($key != $value) {
				if (!$this->_translations) $this->_translations = array();
				$this->_translations[$key] = $value;
			//}
		}
		return $this; 
	}
	/**
	 * Makes options (specified in $options key-value pair array) for HTML element (selected with $selector) 
	 * to be available in client-side scripts. 
	 * @param string $selector
	 * @param array $options
	 * @return Mana_Core_Helper_Js
	 */
	public function options($selector, $options) {
		if (!$this->_options) $this->_options = array();
		if (!isset($this->_options[$selector])) {
			$this->_options[$selector] = $options;
		}
		else {
		    $this->_mergeArrayRecursive($this->_options[$selector], $options);
		}
		return $this; 
	}

	protected function _mergeArrayRecursive(&$target, $source) {
        foreach ($source as $key => $value) {
            if (isset($target[$key]) && is_array($target[$key]) && is_array($value)) {
                $this->_mergeArrayRecursive($target[$key], $value);
            }
            else {
                $target[$key] = $value;
            }
        }

    }

	/**
	 * Returns all the translations registered to be passed to client-side scripts
	 * @return array | null
	 */
	public function getTranslations() { return $this->_translations; }
	/**
	 * Returns all key-value pair arrays registered to be passed to client-side scripts
	 * @return array | null
	 */
	public function getOptions() { return $this->_options; }

	protected function _prepareLayout() {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper('mana_core');

	    $this
	        ->setConfig('url.base', Mage::getUrl('', array('_nosid' => true)))
	        ->setConfig('url.secureBase', Mage::getUrl('', array('_secure' => true, '_nosid' => true)));
        $this
            ->setConfig('ajax.currentRoute', $core->getRoutePath() . $core->getRouteParams());

        if ($value = Mage::getStoreConfig('mana/ajax/google_analytics_account')) {
            $this->setConfig('ga.account', $value);
        }
	    elseif ($value = Mage::getStoreConfig('google/analytics/account')) {
	        $this->setConfig('ga.account', $value);
	    }
	    elseif ($value = Mage::getStoreConfig('aromicon_gua/general/account_id')) {
            $this->setConfig('ga.account', $value);
        }
	    return $this;
	}
}