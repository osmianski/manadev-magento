<?php
/**
 * @category    Mana
 * @package     Mana_Admin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Enter description here ...
 * @author Mana Team
 *
 */
abstract class Mana_Admin_Controller_Crud extends Mage_Adminhtml_Controller_Action {
	protected abstract function _getEntityName();
	
	protected function _registerModel() {
		if (Mage::helper('mana_admin')->isGlobal()) {
			$model = Mage::getModel($this->_getEntityName())->load($this->getRequest()->getParam('id'));
		}
		else {
			$model = Mage::getModel($this->_getEntityName().'_store')->loadByGlobalId(
				$this->getRequest()->getParam('id'), 
				Mage::helper('mana_admin')->getStore()->getId()
			);
		}
		Mage::register('m_crud_model', $model);
		return $model;
	}
}