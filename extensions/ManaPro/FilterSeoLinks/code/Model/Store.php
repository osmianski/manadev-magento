<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterSeoLinks
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Rewrite of Mage_Core_Model_Store which makes store links SEO firendly
 * @author Mana Team
 *
 */
class ManaPro_FilterSeoLinks_Model_Store extends Mage_Core_Model_Store {
	public function getCurrentUrl($fromStore = true) {
		return Mage::getSingleton('core/url')->setEscape(true)->encodeUrl('*/*/*', parent::getCurrentUrl($fromStore));
	}
}