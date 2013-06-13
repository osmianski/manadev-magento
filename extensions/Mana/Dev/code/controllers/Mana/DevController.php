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
        $types = Mage::app()->useCache();
        if (!empty($types)) {
            foreach ($types as $type) {
                $tags = Mage::app()->getCacheInstance()->cleanType($type);
                Mage::dispatchEvent('adminhtml_cache_refresh_type', array('type' => $type));
            }
        }
        Mage::dispatchEvent('adminhtml_cache_flush_all');
        Mage::app()->getCacheInstance()->flush();
        Mage::app()->cleanCache();
        Mage::dispatchEvent('adminhtml_cache_flush_system');
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('mana_dev')->__('All cache types has been cleared.'));
        $this->_redirectReferer();
    }
}