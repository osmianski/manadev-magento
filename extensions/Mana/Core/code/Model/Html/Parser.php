<?php
/**
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Implements naive HTML parser
 * @author Mana Team
 *
 */
class Mana_Core_Model_Html_Parser extends Varien_Object {
	protected $_token; 
	protected $_openedElements = array();
	
	protected function _construct() {
		$this->_token = $this->getReader()->read($this->hasStartsWith() ? $this->getStartsWith() : Mana_Core_Model_Html_State::INITIAL_TEXT);
	}
	protected function _read($initialState, $expect = 0, $allowWhitespace = true) {
		$this->_token = $this->getReader()->read($initialState, $allowWhitespace);
		if ($expect && $this->_token['type'] != $expect) {
			throw new Exception(Mage::helper('mana_core')->__('HTML parser error %s: %s expected%s', 
				Mana_Core_Model_Html_Token::getPosition($this->_token), Mana_Core_Model_Html_Token::getName($expect), 
				$this->getReader()->getSourceAt($this->_token)));
		}
		return $this->_token['type'];
	}
	/**
	 * Content ::= { Normal | SelfClosing | Void | TEXT | CDATA | COMMENT }
	 * Void ::= '<' (area | base | br | col | command | embed | hr | img | input | keygen | link | meta | param | 
	 * 		source | track | wbr) Attributes ('>' | '/>')
	 * Normal ::= '<' NAME Attributes '/>' | ('>' Content '</' ID '>')
	 * Attributes ::= { NAME ['=' VALUE ] } 
	 */
	public function parseContent($parentElement = null) {
		$result = $this->_beforeParsingContent($parentElement);
		while ($this->_token['type'] != Mana_Core_Model_Html_Token::EOF && $this->_token['type'] != Mana_Core_Model_Html_Token::TAG_END) {
			switch ($this->_token['type']) {
				case Mana_Core_Model_Html_Token::TAG_START:
					$this->_afterParsingChildElement($parentElement, $result, $this->parseElement($this->_beforeParsingChildElement($parentElement, $result)));
					break;
				case Mana_Core_Model_Html_Token::CDATA:
					$this->_processCDATA($parentElement, $result, $this->_token);
					$this->_read(Mana_Core_Model_Html_State::INITIAL_TEXT);
					break;
				case Mana_Core_Model_Html_Token::COMMENT:
					$this->_processComment($parentElement, $result, $this->_token);
					$this->_read(Mana_Core_Model_Html_State::INITIAL_TEXT);
					break;
				case Mana_Core_Model_Html_Token::TEXT:
					$this->_processText($parentElement, $result, $this->_token);
					$this->_read(Mana_Core_Model_Html_State::INITIAL_TEXT);
					break;
				default: 
					throw new Exception(Mage::helper('mana_core')->__('HTML parser error %s: unexpected %s%s', 
						Mana_Core_Model_Html_Token::getPosition($this->_token), 
						Mana_Core_Model_Html_Token::getName($this->_token['type']), 
						$this->getReader()->getSourceAt($this->_token)));
			}
		}
		return $this->_afterParsingContent($parentElement, $result);
	}
	public function parseElement($parentContent = null) {
		$result = $this->_beforeParsingElement($parentContent);
		$this->_read(Mana_Core_Model_Html_State::INITIAL, Mana_Core_Model_Html_Token::NAME, false);
		$elementName = $this->_token['text'];
		array_push($this->_openedElements, $elementName);
		$void = Mana_Core_Model_Html_Token::isVoid($elementName);
		$rawText = Mana_Core_Model_Html_Token::isRawText($elementName);
		$this->_processElementName($parentContent, $result, $this->_token, $elementName, $void, $rawText);
		while ($this->_read(Mana_Core_Model_Html_State::INITIAL) == Mana_Core_Model_Html_Token::NAME || ($this->_token['type'] == Mana_Core_Model_Html_Token::EQ)) {
			if ($this->_token['type'] != Mana_Core_Model_Html_Token::EQ) {
				$attributeName = $this->_token['text'];
				$this->_processAttributeName($parentContent, $result, $this->_token, $attributeName);
			}
			else {
				$this->_processAttributeEq($parentContent, $result, $this->_token);
				$this->_read(Mana_Core_Model_Html_State::INITIAL_VALUE, Mana_Core_Model_Html_Token::VALUE, true);
				$attributeValue = $this->_token['text'];
				$this->_processAttributeValue($parentContent, $result, $this->_token, $attributeValue);
			}
		}
		switch ($this->_token['type']) {
			case Mana_Core_Model_Html_Token::TAG_SELF_CLOSE: 
				$this->_processElementClose($parentContent, $result, $this->_token);
				array_pop($this->_openedElements);
				break;
			case Mana_Core_Model_Html_Token::TAG_CLOSE: 
				$this->_processElementClose($parentContent, $result, $this->_token);
				if (!$void) {
					$this->_read($rawText ? $elementName : Mana_Core_Model_Html_State::INITIAL_TEXT);
					$this->_afterParsingChildContent($parentContent, $result, $this->parseContent($this->_beforeParsingChildContent($parentContent, $result)));
					if ($this->_token['type'] != Mana_Core_Model_Html_Token::TAG_END) {
						throw new Exception(Mage::helper('mana_core')->__('HTML parser error %s: %s expected%s', 
							Mana_Core_Model_Html_Token::getPosition($this->_token), 
							Mana_Core_Model_Html_Token::getName(Mana_Core_Model_Html_Token::TAG_END), 
							$this->getReader()->getSourceAt($this->_token)));
					}
					$this->_read(Mana_Core_Model_Html_State::INITIAL, Mana_Core_Model_Html_Token::NAME, false);
					array_pop($this->_openedElements);
					if ($this->_token['text'] != $elementName) {
						if (in_array($this->_token['text'], $this->_openedElements)) {
							$this->getReader()->move(-3 - mb_strlen($this->_token['text']));
							$this->_processElementEnd($parentContent, $result, $this->_token, $elementName);
						}
						else {
							throw new Exception(Mage::helper('mana_core')->__('HTML parser error %s: closing tag for %s expected%s', 
								Mana_Core_Model_Html_Token::getPosition($this->_token), $elementName, 
								$this->getReader()->getSourceAt($this->_token)));
						}
					}
					else {
						$this->_read(Mana_Core_Model_Html_State::INITIAL, Mana_Core_Model_Html_Token::TAG_CLOSE, false);
						$this->_processElementEnd($parentContent, $result, $this->_token, $elementName);
					}
				}
				else {
					array_pop($this->_openedElements);
				}
				break;
			default:
				throw new Exception(Mage::helper('mana_core')->__('HTML parser error %s: %s or %s expected%s', 
					Mana_Core_Model_Html_Token::getPosition($this->_token), 
					Mana_Core_Model_Html_Token::getName(Mana_Core_Model_Html_Token::TAG_SELF_CLOSE), 
					Mana_Core_Model_Html_Token::getName(Mana_Core_Model_Html_Token::TAG_CLOSE), 
					$this->getReader()->getSourceAt($this->_token)));
		}
		$this->_read(Mana_Core_Model_Html_State::INITIAL_TEXT);
		return $this->_afterParsingElement($parentContent, $result);
	}
	
	/* CONTENT CALLBACKS */
	
	protected function _beforeParsingContent($parentElement) {
		return null;
	}
	protected function _afterParsingContent($parentElement, $content) {
		return $content;
	}
	protected function _beforeParsingChildElement($parentElement, $content) {
		return $content;
	}
	protected function _afterParsingChildElement($parentElement, $content, $childElement) {
	}
	protected function _processCDATA($parentElement, $content, $token) {
	}
	protected function _processComment($parentElement, $content, $token) {
	}
	protected function _processText($parentElement, $content, $token) {
	}
	
	/* ELEMENT CALLBACKS */
	
	protected function _beforeParsingElement($parentContent) {
		return null;
	}
	protected function _afterParsingElement($parentContent, $element) {
		return $element;
	}
	protected function _processElementName($parentContent, $element, $token, $elementName, $void, $rawText) {
	}
	protected function _processAttributeName($parentContent, $element, $token, $attributeName) {
	}
	protected function _processAttributeEq($parentContent, $element, $token) {
	}
	protected function _processAttributeValue($parentContent, $element, $token, $attributeValue) {
	}
	protected function _processElementClose($parentContent, $element, $token) {
	}
	protected function _beforeParsingChildContent($parentContent, $element) {
		return $element;
	}
	protected function _afterParsingChildContent($parentContent, $element, $childContent) {
	}
	protected function _processElementEnd($parentContent, $element, $token, $elementName) {
	}
}