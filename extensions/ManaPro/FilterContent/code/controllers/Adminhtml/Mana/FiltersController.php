<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterContent
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Enter description here ...
 * @author Mana Team
 *
 */
class ManaPro_FilterContent_Adminhtml_Mana_FiltersController extends Mana_Admin_Controller_Crud {
	protected function _getEntityName() {
		return 'mana_filters/filter2';
	}
	public function tabContentAction() {
		$model = $this->_registerModel();
		
		// layout
        $this->addActionLayoutHandles();
        $this->loadLayoutUpdates();
        $this->generateLayoutXml()->generateLayoutBlocks();
		$this->_isLayoutLoaded = true;

		// render AJAX result
		$this->renderLayout(); 
	}

    public function contentGridAction() {
        $model = $this->_registerModel();
		if ($edit = $this->getRequest()->getParam('edit')) {
		    $edit = json_decode($edit, true);
		    $this->adminHelper()->processPendingEdits('mana_filters/filter2_value', $edit);
		}
		else {
		    $edit = null;
		}

        // layout
        $this->addActionLayoutHandles();
        $this->loadLayoutUpdates();
        $this->generateLayoutXml()->generateLayoutBlocks();
        $this->_isLayoutLoaded = true;
		$this->getLayout()->getBlock('content_grid')->setData('edit', $edit);

        // render AJAX result
        $this->renderLayout();
    }

    protected function _isAllowed() {
        return true;
    }

    #region Dependencies
    /**
     * @return Mana_Admin_Helper_Data
     */
    public function adminHelper() {
        return Mage::helper('mana_admin');
    }
    #endregion
}