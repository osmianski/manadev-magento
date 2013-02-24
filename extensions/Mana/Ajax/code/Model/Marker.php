<?php
/**
 * @category    Mana
 * @package     Mana_Ajax
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Adds specific class to top level elements
 * @author Mana Team
 *
 */
class Mana_Ajax_Model_Marker extends Mana_Core_Model_Html_Filter {
	protected $_indent = 0;

	protected function _beforeParsingChildElement($parentElement, $content) {
		$this->_indent++;
		return $content;
	}
	protected function _afterParsingChildElement($parentElement, $content, $childElement) {
		$this->_indent--;
	}
	protected function _processText($parentElement, $content, $token) {
		if (trim($token['full_text']) !== '') {
			if ($this->_indent == 0) {
				$this->_filteredOutput .= '<span class="m-block mb-'.$this->getBlockName().'">';
			}
			parent::_processText($parentElement, $content, $token);
			if ($this->_indent == 0) {
				$this->_filteredOutput .= '</span>';
			}
		}
	}
	protected function _beforeParsingElement($parentContent) {
		return new Varien_Object;
	}
	protected function _processAttributeName($parentContent, $element, $token, $attributeName) {
		parent::_processAttributeName($parentContent, $element, $token, $attributeName);
		if ($this->_indent == 1 && !$element->getIsMarked() && strtolower($attributeName) == 'class') {
			if (!$element->getIsExpectingClassValue()) {
				$element->setIsExpectingClassValue(true);
			}
			else { // empty class attribute
				$this->_filteredOutput .= '="m-block mb-'.$this->getBlockName().'"';
				$element->setIsExpectingClassValue(false)->setIsMarked(true);
			}
		}
	}
	protected function _processAttributeValue($parentContent, $element, $token, $attributeValue) {
		// TODO: enclose class attribute values in quotes, otherwise this won't work
		parent::_processAttributeValue($parentContent, $element, $token, $attributeValue);
		/* @var $core Mana_Core_Helper_Data */ $core = Mage::helper(strtolower('Mana_Core'));
		if ($element->getIsExpectingClassValue()) {
			$closingQuote = $core->endsWith($this->_filteredOutput, '"') ? '"' : ($core->endsWith($this->_filteredOutput, "'") ? "'" : '');
			if ($closingQuote != '') {
				$this->_filteredOutput = substr($this->_filteredOutput, 0, strlen($this->_filteredOutput) - strlen($closingQuote));
			}
			$this->_filteredOutput .= ' m-block mb-'.$this->getBlockName().$closingQuote;
			$element->setIsExpectingClassValue(false)->setIsMarked(true);
		}
	}
	protected function _processElementClose($parentContent, $element, $token) {
		if ($this->_indent == 1 && !$element->getIsMarked()) {
			if (!$element->getIsExpectingClassValue()) {
				$this->_filteredOutput .= ' class';
			}
			$this->_filteredOutput .= '="m-block mb-'.$this->getBlockName().'"';
			$element->setIsExpectingClassValue(false)->setIsMarked(true);
		}
		parent::_processElementClose($parentContent, $element, $token);
	}
}