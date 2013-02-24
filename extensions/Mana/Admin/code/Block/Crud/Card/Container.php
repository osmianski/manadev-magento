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
class Mana_Admin_Block_Crud_Card_Container extends Mage_Adminhtml_Block_Widget_Container {
    /**
     * Set template
     */
    public function __construct() {
        parent::__construct();
        $this->setTemplate('mana/admin/container.phtml');
    }
	protected function _addCloseButton() {
		$this->_addButton('close', array('label' => $this->__('Close'), 'class' => 'back m-close-action'));
		Mage::helper('mana_core/js')->options('button.m-close-action', array(
			'redirect_to' => Mage::helper('mana_admin')->getStoreUrl('*/*/index'),
		));
		return $this;
	}
	protected function _addApplyButton() {
		$this->_addButton('apply', array('label' => $this->__('Apply'), 'class' => 'save m-save-action'));
		Mage::helper('mana_core/js')->options('button.m-save-action', array(
			'action' => Mage::helper('mana_admin')->getStoreUrl('*/*/save', 
				array('id' => Mage::app()->getRequest()->getParam('id')), 
				array('ajax' => 1)
			),
		));
		return $this;
	}
	protected function _addSaveButton() {
		$this->_addButton('save', array('label' => $this->__('Save'), 'class' => 'save m-save-and-close-action'));
		Mage::helper('mana_core/js')->options('button.m-save-and-close-action', array(
			'redirect_to' => Mage::helper('mana_admin')->getStoreUrl('*/*/index'),
			'action' => Mage::helper('mana_admin')->getStoreUrl('*/*/save', 
				array('id' => Mage::app()->getRequest()->getParam('id')), 
				array('ajax' => 1)
			),
		));
		return $this;
	}
}