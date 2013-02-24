<?php
/** 
 * @category    Mana
 * @package     Mana_Dev
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * @author Mana Team
 *
 */
class Mana_Dev_Mana_DevController extends Mage_Adminhtml_Controller_Action {
    public function refreshCacheAction() {
        Mage::app()->cleanCache();
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('mana_dev')->__('All cache types has been cleared.'));
        $this->_redirectReferer();
    }
}