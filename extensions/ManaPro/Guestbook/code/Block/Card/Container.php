<?php
/**
 * @category    Mana
 * @package     ManaPro_Guestbook
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Enter description here ...
 * @author Mana Team
 *
 */
class ManaPro_Guestbook_Block_Card_Container extends Mana_Admin_Block_Crud_Card_Container {
    public function __construct() {
        parent::__construct();
        $model = Mage::registry('m_crud_model');
        if ($model->getId()) {
            $this->_headerText = $this->__('Guest Post #%d', $model->getId());
        }
        else {
            $this->_headerText = $this->__('New Guest Post');
        }

    }
	protected function _prepareLayout() {
		$this
		    ->_addCloseButton()
		    ->_addButton('delete', array(
                'label' => $this->__('Delete'),
                'class' => 'delete',
                'onclick' => 'deleteConfirm(\'' . $this->__('Are you sure you want to do this?')
                        . '\', \'' . $this->getDeleteUrl() . '\')',
            ))
		    ->_addApplyButton()
		    ->_addSaveButton();

	}
    public function getDeleteUrl() {
        return $this->getUrl('*/*/delete', array('id' => $this->getRequest()->getParam('id')));
    }
}