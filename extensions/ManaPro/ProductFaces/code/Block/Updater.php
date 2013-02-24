<?php
/**
 * @category    Mana
 * @package     ManaPro_ProductFaces
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Dynamic update scripts
 * @author Mana Team
 *
 */
class ManaPro_ProductFaces_Block_Updater extends Mage_Adminhtml_Block_Template {
	protected function _construct() {
		$this->setTemplate('manapro/productfaces/updater.phtml');
	}

	public function getUpdateUrl() {
		return $this->getUrl('adminhtml/representing_products/update', array('_current' => true));
	}
	public function getHideWarningUrl() {
		return $this->getUrl('adminhtml/representing_products/hideWarning', array('_current' => true));
	}
}