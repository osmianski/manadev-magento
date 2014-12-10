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
class Mana_Core_Profiler2 {
    static protected $_stack = array();
    public static function enabled() {
        return defined('MANA_PROFILER');
    }
	public static function start($name, $logQueries = false) {
        if (self::enabled()) {
            array_push(self::$_stack, compact('name', 'logQueries'));
            if ($logQueries) {
                self::logQueries(true);
            }
            Varien_Profiler::start($name);
        }
	}
	public static function stop() {
        if (self::enabled()) {
            /* @var $name string */
            /* @var $logQueries bool */
            extract(array_pop(self::$_stack));
            Varien_Profiler::stop($name);
            if ($logQueries) {
                self::logQueries(false);
            }
        }
    }

    public static function logQueries($flag) {
        $db = Mage::getSingleton('core/resource')->getConnection('read');
        if (method_exists($db, 'debug')) {
            $db->debug($flag);
        }
    }
}