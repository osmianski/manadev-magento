<?php
/**
 * @category    Mana
 * @package     Mana_Admin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Admin_Controller extends Mage_Adminhtml_Controller_Action {
    protected function _defaultPageAction() {
        /* @var $adminPageHelper Mana_Admin_Helper_Page */
        $adminPageHelper = Mage::helper('mana_admin/page');

        // layout
        $update = $this->getLayout()->getUpdate();
        $update->addHandle('default');
        $update->addHandle('mana_admin_page');
        $this->addActionLayoutHandles();

        $this->loadLayoutUpdates();
        $this->generateLayoutXml()->generateLayoutBlocks();
        $this->_isLayoutLoaded = true;
        $this->_initLayoutMessages('adminhtml/session');

        // rendering
        if ($pageBlock = $this->getLayout()->getBlock('page')) {
            /* @var $pageBlock Mana_Entity_Block_Adminhtml_Page */
            if ($pageBlock->getTitleGroup()) {
                $this->_title($pageBlock->getTitleGroup());
            }
            if ($pageBlock->getTitle()) {
                $this->_title($pageBlock->getTitle());
            }
            if ($pageBlock->getMenu()) {
                $this->_setActiveMenu($pageBlock->getMenu());
            }
        }

        $this->renderLayout();
    }

    public function norouteAction($coreRoute = null) {
        /* @var $adminPageHelper Mana_Admin_Helper_Page */
        $adminPageHelper = Mage::helper('mana_admin/page');

        if ($adminPageHelper->getPageLayout($this->getRequest())) {
            $this->_defaultPageAction();
        }
        else {
            parent::norouteAction($coreRoute);
        }
    }

    public function hasAction($action) {
        /* @var $adminPageHelper Mana_Admin_Helper_Page */
        $adminPageHelper = Mage::helper('mana_admin/page');

        if ($adminPageHelper->getPageLayout($this->getRequest())) {
            return true;
        }
        else {
            return parent::hasAction($action);
        }
    }
}