<?php
/** 
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Admin_ManaController extends Mage_Adminhtml_Controller_Action {
    public function hideMessageAction() {

        $this->utilsHelper()->setStoreConfig('mana/message/'. $this->getRequest()->getParam('message_key'), 0);
        Mage::app()->cleanCache();
        $this->getResponse()->setBody('ok');
    }

    #region Dependencies
    /**
     * @return Mana_Core_Helper_Utils
     */
    public function utilsHelper() {
        return Mage::helper('mana_core/utils');
    }
    #endregion
}