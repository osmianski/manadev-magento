<?php
/**
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Source for options of filter being filterable
 * @author Mana Team
 *
 */
class Mana_Core_Model_Source_Js extends Mana_Core_Model_Source_Abstract {
	protected function _getAllOptions() {
	    /* @var $t Mana_Core_Helper_Data */
	    $t = Mage::helper(strtolower('Mana_Core'));
		return array(
            array('value' => 'min_ondemand', 'label' => $t->__('Load if required, minified version (recommended)')),
            array('value' => 'min_everywhere', 'label' => $t->__('Load on all pages, minified version')),
            array('value' => 'full_ondemand', 'label' => $t->__('Load if required, full version with comments')),
            array('value' => 'full_everywhere', 'label' => $t->__('Load on all pages, full version with comments')),
            array('value' => 'unload', 'label' => $t->__('Do not load')),
            array('value' => 'skip', 'label' => $t->__('Take action as defined in theme layout XML files')),
        );
	}
}