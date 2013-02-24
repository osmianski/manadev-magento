<?php
/**
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Basic HTML filter which output the same HTML as inputed
 * @author Mana Team
 *
 */
class Mana_Core_Model_Html_Filter extends Mana_Core_Model_Html_Parser {
	protected $_filteredOutput = '';
	public function getFilteredOutput() {
		return $this->_filteredOutput;
	}
	protected function _processCDATA($parentElement, $content, $token) {
		$this->_filteredOutput .= $token['full_text'];
	}
	protected function _processComment($parentElement, $content, $token) {
		$this->_filteredOutput .= $token['full_text'];
	}
	protected function _processText($parentElement, $content, $token) {
		$this->_filteredOutput .= $token['full_text'];
	}
	protected function _processElementName($parentContent, $element, $token, $elementName, $void, $rawText) {
		$this->_filteredOutput .= '<'.$token['full_text'];
	}
	protected function _processAttributeName($parentContent, $element, $token, $attributeName) {
		$this->_filteredOutput .= $token['full_text'];
	}
	protected function _processAttributeEq($parentContent, $element, $token) {
		$this->_filteredOutput .= $token['full_text'];
	}
	protected function _processAttributeValue($parentContent, $element, $token, $attributeValue) {
		$this->_filteredOutput .= $token['full_text'];
	}
	protected function _processElementClose($parentContent, $element, $token) {
		$this->_filteredOutput .= $token['full_text'];
	}
	protected function _processElementEnd($parentContent, $element, $token, $elementName) {
		$this->_filteredOutput .= '</'.$elementName.'>';
	}
}