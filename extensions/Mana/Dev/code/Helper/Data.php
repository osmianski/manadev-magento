<?php
/** 
 * @category    Mana
 * @package     Mana_Dev
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Generic helper functions for Mana_Dev module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class Mana_Dev_Helper_Data extends Mage_Core_Helper_Abstract {
	public function getRefreshCacheUrl() {
	    return Mage::getModel('adminhtml/url')->getUrl('*/mana_dev/refreshCache', array(
	        Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => 
	            Mage::helper('core')->urlEncode(Mage::helper('core/url')->getCurrentUrl()),
	    ));
	}
}