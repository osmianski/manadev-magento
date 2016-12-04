<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_DomainChangeController extends Mage_Core_Controller_Front_Action
{

    public function confirmAction() {
        $hash = $this->getRequest()->getParam('hash');
        /** @var Local_Manadev_Model_Downloadable_Item $link_purchased_item */
        $link_purchased_item = Mage::getModel('downloadable/link_purchased_item')->load($hash, 'm_pending_hash');
        if($link_purchased_item->getId()) {
            $link_purchased_item
                ->setData('m_pending_hash', null)
                ->updateStoreInfoFromPending();
            $this->_getSession()->addSuccess($this->localHelper()->__("New domain registration information has been applied."));
        } else {
            $this->_getSession()->addError($this->localHelper()->__("Pending domain registration information not found. Please check if it is already applied."));
        }
        return $this->_redirect('');
    }

    /**
     * @return Local_Manadev_Helper_Data
     */
    protected function localHelper() {
        return Mage::helper('local_manadev');
    }

    /**
     * @return Mage_Core_Model_Abstract
     */
    protected function _getSession() {
        return Mage::getSingleton('core/session');
    }
}