<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterColors
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Enter description here ...
 * @author Mana Team
 *
 */
class ManaPro_FilterColors_Mana_FiltersController extends Mana_Admin_Controller_Crud {
	protected function _getEntityName() {
		return 'mana_filters/filter2';
	}
	public function tabColorsAction() {
		$model = $this->_registerModel();
		
		// layout
        $this->addActionLayoutHandles();
        $this->loadLayoutUpdates();
        $this->generateLayoutXml()->generateLayoutBlocks();
		$this->_isLayoutLoaded = true;

		// render AJAX result
		$this->renderLayout(); 
	}
	public function tabColorsGridAction() {
		$model = $this->_registerModel();
		if ($edit = $this->getRequest()->getParam('m-edit')) {
		    $edit = json_decode(base64_decode($edit), true);
		    Mage::helper('mana_admin')->processPendingEdits('mana_filters/filter2_value', $edit);
		}
		else {
		    $edit = null;
		}
		
		// layout
        $this->addActionLayoutHandles();
        $this->loadLayoutUpdates();
        $this->generateLayoutXml()->generateLayoutBlocks();
		$this->_isLayoutLoaded = true;
		$this->getLayout()->getBlock('colors_grid')->setEdit($edit);
		// render AJAX result
		$this->renderLayout(); 
	}

	protected function _isAllowed() {
		return true;
	}
}