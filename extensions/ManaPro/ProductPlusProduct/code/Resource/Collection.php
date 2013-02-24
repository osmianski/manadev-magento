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
class ManaPro_ProductPlusProduct_Resource_Collection extends Mana_ProductLists_Resource_Collection {
	protected function _getLinkType() {
		return ManaPro_ProductPlusProduct_Resource_Setup::LINK_TYPE;
	}
	protected function _beforeLoad() {
		$this->addFieldToFilter('type_id', array('nin' => array('bundle')));
		$this->addFieldToFilter('required_options', 0);
		return parent::_beforeLoad();
	}
}