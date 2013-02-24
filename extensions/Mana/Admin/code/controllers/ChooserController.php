<?php
/**
 * @category    Mana
 * @package     Mana_Admin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * @author Mana Team
 *
 */
class Mana_Admin_ChooserController extends Mage_Adminhtml_Controller_Action {
    public function productAction() {
        $this->getResponse()->setBody(Mage::helper('mana_admin')->getProductChooserHtml());
    }
    public function cmsBlockAction() {
        $this->getResponse()->setBody(Mage::helper('mana_admin')->getCmsBlockChooserHtml());
    }
}