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
class Mana_Core_Profiler {
	public static function start() {
		$args = func_get_args();
		$name = '';
		foreach ($args as $arg) {
			if ($name) $name .= '::';
			$name .= $arg;
			Varien_Profiler::start($name);
		}
	}
	public static function stop() {
		$args = func_get_args();
		$name = '';
		foreach ($args as $arg) {
			if ($name) $name .= '::';
			$name .= $arg;
			Varien_Profiler::stop($name);
		}
	}
}