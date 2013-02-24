<?php
/**
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Possible constants for attribute scope
 * @author Mana Team
 *
 */
class Mana_Core_Model_Attribute_Scope {
	private function __construct() {}
    const _STORE   = 0;
    const _GLOBAL  = 1;

    const _STORE_TEXT = 'store';
    const _GLOBAL_TEXT = 'global';
    const _SINGLE_STORE_TEXT = 'single_store';
}