<?php
/**
 * @category    Mana
 * @package     ManaPro_ProductPlusProduct
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Helper functions for setup scripts
 * @author Mana Team
 *
 */
class ManaPro_ProductPlusProduct_Resource_Setup extends Mana_ProductLists_Resource_Setup {
	const LINK_TYPE = 'm_productplusproduct';
	public function getDefaultProductLinks() {
		return array(
			self::LINK_TYPE => array(
				'attributes' => array(
					// FUTURE: 'qty' => array('backend_type' => 'decimal'),
					'position' => array('backend_type' => 'decimal'),
				),
			), 
		);
	}
}