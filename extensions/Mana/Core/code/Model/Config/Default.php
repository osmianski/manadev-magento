<?php
/**
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Default value provider which gets value from global configuration
 * @author Mana Team
 *
 */
class Mana_Core_Model_Config_Default {
	public function getDefaultValue($model, $attributeCode, $path) {
		return Mage::getStoreConfig($path);
	}
	public function getUseDefaultLabel() {
		return Mage::helper('mana_core')->__('Use System Configuration');
	}
}