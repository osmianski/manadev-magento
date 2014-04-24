<?php
/** 
 * @category    Mana
 * @package     Local_Manadev
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

include_once BP.'/app/code/core/Mage/Customer/controllers/AccountController.php';


/**
 * @author Mana Team
 *
 */
class Local_Manadev_AccountController extends Mage_Customer_AccountController {
    protected function _loginPostRedirect()
    {
        $session = $this->_getSession();

        if (!$session->getBeforeAuthUrl() || $session->getBeforeAuthUrl() == Mage::getBaseUrl()) {
            // Redirect customer to the last page visited after logging in
                $referer = $this->getRequest()->getParam(Mage_Customer_Helper_Data::REFERER_QUERY_PARAM_NAME);
                if ($referer) {
                    $referer = Mage::helper('core')->urlDecode($referer);
                    if ($this->_isUrlInternal($referer)) {
                        $session->setBeforeAuthUrl($referer);
                    }
                }
        }
        parent::_loginPostRedirect();
    }
}