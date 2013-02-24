<?php
/**
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Enter description here ...
 * @author Mana Team
 *
 */
class Mana_Core_Block_List extends Mage_Core_Block_Text_List {
	protected function _toHtml() {
		if ($this->_visible) {
			return parent::_toHtml();
		}
		else {
			return '';
		}
	}
	
	protected $_visible = true;
	public function showIfFlagSet($param) {
		$this->_visible = Mage::getStoreConfigFlag($param);
		return $this;
	} 
	public function showIfFlagNotSet($param) {
		$this->_visible = ! Mage::getStoreConfigFlag($param);
		return $this;
	} 
	public function showIfValueEquals($param, $value) {
		$this->_visible = Mage::getStoreConfig($param) == $value;
		return $this;
	} 
	public function showIfValueNotEquals($param, $value) {
		$this->_visible = Mage::getStoreConfig($param) != $value;
		return $this;
	} 
}