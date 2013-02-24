<?php
/**
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * HTML scanner states
 * @author Mana Team
 *
 */
class Mana_Core_Model_Html_State {
	// initial states
	const INITIAL = 0; // default, should recognize name, =, >, />
	const INITIAL_TEXT = 1;
	const INITIAL_VALUE = 2;
	const INITIAL_RAWTEXT = 3;
	
	// text states
	const CDATA = 5;
	const COMMENT = 6;
	const TEXT = 7;
	const RAWTEXT = 8;
	
	// element states
	const NAME = 9;
	const SINGLE_QUOTED_VALUE = 10;
	const DOUBLE_QUOTED_VALUE = 11;
	const UNQUOTED_VALUE = 12;
	
	// final state
	const FINISHED = 99;
}