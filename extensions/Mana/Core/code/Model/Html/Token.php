<?php
/**
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * HTML tokens recognized by scanner
 * @author Mana Team
 *
 */
class Mana_Core_Model_Html_Token {
    protected static $_sourceLinesBefore = 5;
	const NOTHING = 0;
	const EOF = 1;
	const TAG_START = 2; // <
	const TAG_END = 3; // </
	const TAG_SELF_CLOSE = 4; // />
	const TAG_CLOSE = 5; // >
	const EQ = 6; // =
	const CDATA = 7; // <![CDATA[ some text ]]>
	const COMMENT = 8; // <!-- some text -->
	const TEXT = 9;
	const NAME = 10;
	const VALUE = 11; // value | 'value' | "value"
	
	protected static $_names = array(
		self::NOTHING => 'nothing',
		self::EOF => 'end-of-file',
		self::TAG_START => '"<"',
		self::TAG_END => '"</"',
		self::TAG_SELF_CLOSE => '"/>"',
		self::TAG_CLOSE => '">"',
		self::EQ => '"="',
		self::CDATA => 'CDATA section (<![CDATA[ ]]>)',
		self::COMMENT => 'comment (<!-- -->)',
		self::TEXT => 'raw text',
		self::NAME => 'element or attribute name',
		self::VALUE => 'attribute value',
		);
	public static function getName($token) {
		return Mage::helper('mana_core')->__(self::$_names[$token]);
	}
	protected static $_voidElements = array('area', 'base', 'br', 'col', 'command', 'embed', 'hr', 'img', 
		'input', 'keygen', 'link', 'meta', 'param', 'source', 'track', 'wbr', '!doctype');
	protected static $_rawTextElements = array('script', 'style', 'title', 'textarea');
	public static function isVoid($elementName) {
		return in_array(strtolower($elementName), self::$_voidElements);
	}
	public static function isRawText($elementName) {
		return in_array(strtolower($elementName), self::$_rawTextElements);
	}
	public static function getPosition($token) {
		return sprintf('(%s, %s)', $token['column'], $token['line']);
	}
	public static function getSourceAt(&$source, $token, $tabWidth) {
		$result = "\n";
		$lines = explode("\n", str_replace("\t", str_repeat(' ', $tabWidth), $source));
		for ($i = 0; $i < self::$_sourceLinesBefore; $i++) {
		    $line = $token['line'] - (self::$_sourceLinesBefore - $i + 1);
            if ($line >= 0) {
                $result .= $lines[$line]."\n";
            }
		}
		$result .= $lines[$token['line'] - 1]."\n";
		if ($token['column'] > 1) {
			$result .= str_repeat(' ', $token['column'] - 1);
		}
		$result .= str_repeat('-', $token['end_pos'] <= $token['pos'] ? 1 : 
			($token['end_pos'] - $token['pos'] + $token['column'] - 1 >= mb_strlen($lines[$token['line'] - 1]) ? 
				mb_strlen($lines[$token['line'] - 1]) - ($token['column'] - 1) : 
				$token['end_pos'] - $token['pos']));
		if ($token['line'] < count($lines)) {
			$result .= "\n".$lines[$token['line']];
		}
		return $result;
	}
}