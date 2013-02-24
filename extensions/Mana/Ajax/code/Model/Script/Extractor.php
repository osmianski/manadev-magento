<?php
/**
 * @category    Mana
 * @package     Mana_Ajax
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Strips out script elements out of given HTML markup
 * @author Mana Team
 *
 */
class Mana_Ajax_Model_Script_Extractor extends Mana_Core_Model_Html_Filter {
	protected $_insideScript = false;
	protected $_outputBeforeScript = '';
	protected $_extractedScripts = '';
	public function getExtractedScripts() {
		return $this->_extractedScripts;
	}
	

	protected function _processText($parentElement, $content, $token) {
		if (trim($token['full_text']) != '') {
			if ($this->_insideScript) {
				$this->_extractedScripts .= $token['full_text'].";\n";
			}
			else {
				parent::_processText($parentElement, $content, $token);
			}
		}
	}
	protected function _processElementName($parentContent, $element, $token, $elementName, $void, $rawText) {
		if (strtolower($elementName) == 'script') {
			$this->_outputBeforeScript = $this->_filteredOutput;
			$this->_insideScript = true;
		}
		else {
			parent::_processElementName($parentContent, $element, $token, $elementName, $void, $rawText);
		}
	}
	protected function _processElementEnd($parentContent, $element, $token, $elementName) {
		if ($this->_insideScript) {
			$this->_insideScript = false;
			$this->_filteredOutput = $this->_outputBeforeScript;
		}
		else {
			parent::_processElementEnd($parentContent, $element, $token, $elementName);
		}
	}
}