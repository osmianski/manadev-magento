<?php
/**
 * @category    Mana
 * @package     ManaPro_ProductPlusProduct
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Enter description here ...
 * @author Mana Team
 *
 */
class ManaPro_ProductPlusProduct_Model_Link {
	public function validate(&$errors, $product, $id, $fields, $linkKey, $options) {
		//if (isset($fields['position']) && !is_numeric($fields['position'])) {
		//	$errors[] = Mage::helper('mana_productlists')->__(
		//		'Position should be a number, but %s is not (see %s tab, ID %s).', 
		//		$fields['position'], (string)$options->tab_title, $id);
		//}
	}
	public function beforeSave($product, $links, $options) {
		$max = 1;
		foreach ($links as $id => $fields) {
			if (isset($fields['position']) && is_numeric($fields['position']) && $fields['position'] > $max) {
				$max = $fields['position'];
			}
		}
		foreach ($links as $id => &$fields) {
			if (isset($fields['position']) && !is_numeric($fields['position'])) {
				$fields['position'] = ++$max;
			}
        }
        return $links;
	}
}