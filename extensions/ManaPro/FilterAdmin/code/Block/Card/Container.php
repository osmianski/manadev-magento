<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterAdmin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Enter description here ...
 * @author Mana Team
 *
 */
class ManaPro_FilterAdmin_Block_Card_Container extends Mana_Admin_Block_Crud_Card_Container {
    public function __construct() {
        parent::__construct();
        $model = Mage::registry('m_crud_model');
        $this->_headerText = $this->__('%s - Layered Navigation Filter', $model->getName());
    }
	protected function _prepareLayout() {
		$this->_addCloseButton()->_addApplyButton()->_addSaveButton();
	}
}