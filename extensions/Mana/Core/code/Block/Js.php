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
			foreach ($options as $key => $value) {
				$this->_options[$selector][$key] = $value;
			}
		}
		return $this; 
	}
	/**
	 * Returns all the translations registered to be passed to client-side scripts
	 * @var array | null
	 */
	public function getTranslations() { return $this->_translations; }
	/**
	 * Returns all key-value pair arrays registered to be passed to client-side scripts
	 * @var array | null
	 */
	public function getOptions() { return $this->_options; }
}