<?php
/**
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Naive HTML tokenizer
 * @author Mana Team
 *
 */
class Mana_Core_Model_Html_Reader {
	protected $_source;
	protected $_state;
	protected $_pos;
	protected $_ch;
	protected $_length;
	protected $_tabWidth = 4;
	protected $_line;
	protected $_column;
	public function setSource($source) {
		$this->_source = $source;
		$this->_state = Mana_Core_Model_Html_State::INITIAL;
		$this->_length = mb_strlen($source);
		$this->_pos = -1;
		$this->_line = 1;
		$this->_column = 0;
		$this->_ch = $this->_read();
		return $this;
	}
	
	public function read($initialState, $allowWhitespace = true) {
		if (is_string($initialState)) {
			$state = Mana_Core_Model_Html_State::INITIAL_RAWTEXT;
			$rawElement = strtolower($initialState);
		}
		else {
			$state = $initialState;
		}
		$startPos = $this->_pos;
		$token = array(
			'pos' => $this->_pos,
			'line' => $this->_line,
			'column' => $this->_column,
		// other valid indexes: type, text, full_text
		); 
		$readNext = true;
		while ($state != Mana_Core_Model_Html_State::FINISHED) {
			$ch = $this->_ch !== false ? ord($this->_ch) : false;
			switch ($state) {
				case Mana_Core_Model_Html_State::INITIAL_TEXT:
					if ($ch === false) {
						$token['type'] = Mana_Core_Model_Html_Token::EOF;
						$readNext = false;
						$state = Mana_Core_Model_Html_State::FINISHED;
					}
					elseif ($ch == ord('<')) {
						// here we assume we have enough characters in read buffer. It is always the case for now,
						// later it may break if we work with underlying stream, not memory buffer
						if (mb_substr($this->_source, $this->_pos, 2) == '</') {
							$this->_move(1);
							$token['type'] = Mana_Core_Model_Html_Token::TAG_END;
							$state = Mana_Core_Model_Html_State::FINISHED;
						}
						elseif (mb_substr($this->_source, $this->_pos, 8) == '<![CDATA') {
							$this->_move(7);
							$token['type'] = Mana_Core_Model_Html_Token::CDATA;
							$state = Mana_Core_Model_Html_State::CDATA;
						}
						elseif (mb_substr($this->_source, $this->_pos, 4) == '<!--') {
							$this->_move(3);
							$token['type'] = Mana_Core_Model_Html_Token::COMMENT;
							$state = Mana_Core_Model_Html_State::COMMENT;
						}
						else {
							$token['type'] = Mana_Core_Model_Html_Token::TAG_START;
							$state = Mana_Core_Model_Html_State::FINISHED;
						}
					}
					else {
						$token['type'] = Mana_Core_Model_Html_Token::TEXT;
						$state = Mana_Core_Model_Html_State::TEXT;
					}
					break;
					
				case Mana_Core_Model_Html_State::INITIAL:
					if ($ch == ord(' ') || $ch == ord("\r") || $ch == ord("\t") || $ch == ord("\n") || $ch == ord("\f")) {
						if (!$allowWhitespace) {
							$token['end_pos'] = $this->_pos;
							throw new Exception(Mage::helper('mana_core')->__('HTML read error %s: whitespace not expected%s', 
								Mana_Core_Model_Html_Token::getPosition($token), 
								$this->getSourceAt($token)));
						}
					}
					else {
						$token['pos'] = $this->_pos;
						$token['line'] = $this->_line;
						$token['column'] = $this->_column;
						if ($ch === false) {
							$token['type'] = Mana_Core_Model_Html_Token::EOF;
							$readNext = false;
							$state = Mana_Core_Model_Html_State::FINISHED;
						}
						elseif ($ch == ord('>')) {
							$token['type'] = Mana_Core_Model_Html_Token::TAG_CLOSE;
							$state = Mana_Core_Model_Html_State::FINISHED;
						}
						elseif($ch == ord('/') && mb_substr($this->_source, $this->_pos, 2) == '/>') {
							$this->_move(1);
							$token['type'] = Mana_Core_Model_Html_Token::TAG_SELF_CLOSE;
							$state = Mana_Core_Model_Html_State::FINISHED;
						}
						elseif ($ch == ord('=')) {
							$token['type'] = Mana_Core_Model_Html_Token::EQ;
							$state = Mana_Core_Model_Html_State::FINISHED;
						}
						elseif ($ch == ord('!') || ord('a') <= $ch && $ch <= ord('z') || ord('A') <= $ch && $ch <= ord('Z')) {
							$state = Mana_Core_Model_Html_State::NAME;
							$token['type'] = Mana_Core_Model_Html_Token::NAME;
						}
						else {
							$token['end_pos'] = $this->_pos;
							throw new Exception(Mage::helper('mana_core')->__('HTML read error %s: unexpected character%s', 
								Mana_Core_Model_Html_Token::getPosition($token), 
								$this->getSourceAt($token)));
						}
					}
					break;
				case Mana_Core_Model_Html_State::INITIAL_VALUE:
					if ($ch == ord(' ') || $ch == ord("\r") || $ch == ord("\t") || $ch == ord("\n") || $ch == ord("\f")) {
						if (!$allowWhitespace) {
							$token['end_pos'] = $this->_pos;
							throw new Exception(Mage::helper('mana_core')->__('HTML read error %s: whitespace not expected%s', 
								Mana_Core_Model_Html_Token::getPosition($token), 
								$this->getSourceAt($token)));
						}
					}
					else {
						$token['pos'] = $this->_pos;
						$token['line'] = $this->_line;
						$token['column'] = $this->_column;
						$token['type'] = Mana_Core_Model_Html_Token::VALUE;
						if ($ch === false) {
							$token['type'] = Mana_Core_Model_Html_Token::EOF;
							$readNext = false;
							$state = Mana_Core_Model_Html_State::FINISHED;
						}
						elseif ($ch == ord("'")) {
							$state = Mana_Core_Model_Html_State::SINGLE_QUOTED_VALUE;
							$token['pos']++; $token['column']++;
						}
						elseif ($ch == ord('"')) {
							$state = Mana_Core_Model_Html_State::DOUBLE_QUOTED_VALUE;
							$token['pos']++; $token['column']++;
						}
						elseif (!($ch == ord('=') || $ch == ord('<') || $ch == ord('>') || $ch == ord('`'))) {
							$state = Mana_Core_Model_Html_State::UNQUOTED_VALUE;
						}
						else {
							$token['end_pos'] = $this->_pos;
							throw new Exception(Mage::helper('mana_core')->__('HTML read error %s: unexpected character%s', 
								Mana_Core_Model_Html_Token::getPosition($token), 
								$this->getSourceAt($token)));
						}
					}
					break;
				case Mana_Core_Model_Html_State::INITIAL_RAWTEXT:
					if ($ch === false) {
						$token['type'] = Mana_Core_Model_Html_Token::EOF;
						$readNext = false;
						$state = Mana_Core_Model_Html_State::FINISHED;
					}
					else {
						$token['type'] = Mana_Core_Model_Html_Token::TEXT;
						$state = Mana_Core_Model_Html_State::RAWTEXT;
						if ($ch == ord('<') && strtolower(mb_substr($this->_source, $this->_pos, 2 + mb_strlen($rawElement))) == '</'.$rawElement) {
							if (mb_strlen($this->_source) > $this->_pos + 2 + mb_strlen($rawElement)) {
								$nextCh = ord(mb_substr($this->_source, $this->_pos + 2 + mb_strlen($rawElement), 1));
								if ($nextCh == ord('>') || $nextCh == ord('/') || 
									$ch == ord(' ') || $ch == ord("\r") || $ch == ord("\t") || $ch == ord("\n") || $ch == ord("\f"))
								{
									$readNext = false;
									$state = Mana_Core_Model_Html_State::FINISHED;
								}
							}
						} 
					}
					break;
					
				case Mana_Core_Model_Html_State::CDATA:
					if ($ch === false) {
						$token['end_pos'] = $this->_pos;
						throw new Exception(Mage::helper('mana_core')->__('HTML read error %s: unexpected end of text%s', 
							Mana_Core_Model_Html_Token::getPosition($token), 
							$this->getSourceAt($token)));
					}
					elseif ($ch == ord(']') && mb_substr($this->_source, $this->_pos, 3) == ']]>') {
						$this->_move(2);
						$state = Mana_Core_Model_Html_State::FINISHED;
					}
					break;
				case Mana_Core_Model_Html_State::COMMENT:
					if ($ch === false) {
						$token['end_pos'] = $this->_pos;
						throw new Exception(Mage::helper('mana_core')->__('HTML read error %s: unexpected end of text%s', 
							Mana_Core_Model_Html_Token::getPosition($token), 
							$this->getSourceAt($token)));
					}
					elseif ($ch == ord('-') && mb_substr($this->_source, $this->_pos, 3) == '-->') {
						$this->_move(2);
						$state = Mana_Core_Model_Html_State::FINISHED;
					}
					break;
				case Mana_Core_Model_Html_State::TEXT:
					if ($ch === false || $ch == ord('<')) {
						$readNext = false;
						$state = Mana_Core_Model_Html_State::FINISHED;
					}
					break;
				case Mana_Core_Model_Html_State::RAWTEXT:
					if ($ch === false) {
						$readNext = false;
						$state = Mana_Core_Model_Html_State::FINISHED;
					}
					elseif ($ch == ord('<') && strtolower(mb_substr($this->_source, $this->_pos, 2 + mb_strlen($rawElement))) == '</'.$rawElement) {
						if (mb_strlen($this->_source) > $this->_pos + 2 + mb_strlen($rawElement)) {
							$nextCh = ord(mb_substr($this->_source, $this->_pos + 2 + mb_strlen($rawElement), 1));
							if ($nextCh == ord('>') || $nextCh == ord('/') || 
								$ch == ord(' ') || $ch == ord("\r") || $ch == ord("\t") || $ch == ord("\n") || $ch == ord("\f"))
							{
								$readNext = false;
								$state = Mana_Core_Model_Html_State::FINISHED;
							}
						}
					} 
					break;
										
				case Mana_Core_Model_Html_State::NAME:
					if ($ch === false || !($ch == ord('!') || ord('a') <= $ch && $ch <= ord('z') || ord('A') <= $ch && $ch <= ord('Z')
						|| $ch == ord('_') || $ch == ord('-') || $ch == ord(':') || ord('0') <= $ch && $ch <= ord('9')))
					{
						$readNext = false;
						$state = Mana_Core_Model_Html_State::FINISHED;
					}
					break;
				case Mana_Core_Model_Html_State::UNQUOTED_VALUE:
					if ($ch === false || $ch == ord('"') || $ch == ord("'") ||  
						$ch == ord(' ') || $ch == ord("\r") || $ch == ord("\t") || $ch == ord("\n") || $ch == ord("\f") ||
						$ch == ord('=') || $ch == ord('<') || $ch == ord('>') || $ch == ord('`')) 
					{
						$readNext = false;
						$state = Mana_Core_Model_Html_State::FINISHED;
					}
					break;
				case Mana_Core_Model_Html_State::SINGLE_QUOTED_VALUE:
					if ($ch === false) {
						$token['end_pos'] = $this->_pos;
						throw new Exception(Mage::helper('mana_core')->__('HTML read error %s: unexpected end of text%s', 
							Mana_Core_Model_Html_Token::getPosition($token), 
							$this->getSourceAt($token)));
					}
					elseif ($ch == ord("'")) {
						$state = Mana_Core_Model_Html_State::FINISHED;
						$token['end_pos'] = $this->_pos;
					}
					break;
				case Mana_Core_Model_Html_State::DOUBLE_QUOTED_VALUE:
					if ($ch === false) {
						$token['end_pos'] = $this->_pos;
						throw new Exception(Mage::helper('mana_core')->__('HTML read error %s: unexpected end of text%s', 
							Mana_Core_Model_Html_Token::getPosition($token), 
							$this->getSourceAt($token)));
					}
					elseif ($ch == ord('"')) {
						$state = Mana_Core_Model_Html_State::FINISHED;
						$token['end_pos'] = $this->_pos;
					}
					break;
					
				default: throw new Exception('Not implemented');
			}
			if ($readNext) $this->_ch = $this->_read();
		}
		if (!isset($token['end_pos'])) {
			$token['end_pos'] = $this->_pos;
		}
		$token['text'] = $token['end_pos'] == $token['pos'] ? '' : mb_substr($this->_source, $token['pos'], $token['end_pos'] - $token['pos']);
		$token['full_text'] = $this->_pos == $startPos ? '' : mb_substr($this->_source, $startPos, $this->_pos - $startPos);
		return $token;
	}
	
	protected function _read() {
		$ch = ++$this->_pos < $this->_length ? mb_substr($this->_source, $this->_pos, 1) : false;
		if ($ch == "\n") {
			$this->_line++;
			$this->_column = 0;
		}
		elseif ($ch == "\t") {
			$this->_column += $this->_tabWidth;
		}
		else {
			$this->_column++;
		}
		return $ch;	
	}
	protected function _move($offset) {
		$this->_pos += $offset;
		$this->_column += $offset;
	}
	public function move($offset) {
		$this->_move($offset);
		$this->_ch = $this->_read();
	}
	public function getSourceAt($token) {
		return Mana_Core_Model_Html_Token::getSourceAt($this->_source, $token, $this->_tabWidth);
	}
}