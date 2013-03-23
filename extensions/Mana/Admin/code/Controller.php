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

        /* @var $db Mana_Db_Helper_Data */
        $db = Mage::helper('mana_db');

        /* @var $js Mana_Core_Helper_Js */
        $js = Mage::helper('mana_core/js');

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
            /* @var $pageBlock Mana_Admin_Block_Page */
            if ($pageBlock->getTitleGroup()) {
                $this->_title($pageBlock->getTitleGroup());
            }
            if ($pageBlock->getTitle()) {
                $this->_title($pageBlock->getTitle());
            }
            if ($pageBlock->getMenu()) {
                $this->_setActiveMenu($pageBlock->getMenu());
            }

            if ($pageBlock->getBeginEditingSession() && !$db->getInEditing()) {
                $db->setInEditing();
                $js->setConfig('editSessionId', $db->beginEditing());
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

    public function saveAction() {
        $this->_loadLayout(array($this, '_saveResponse'));
    }

    public function _saveResponse() {
        try {
            $this->_save();
        } catch (Mage_Core_Exception $e) {
            $this->getResponse()->setBody(json_encode(array('error' => true, 'message' => $e->getMessage())));
        }
    }

    protected $_actionLayout;

    protected function _loadLayout($callback, $action = null) {
        /* @var $adminPageHelper Mana_Admin_Helper_Page */
        $adminPageHelper = Mage::helper('mana_admin/page');
        $this->_actionLayout = $adminPageHelper->getActionLayout($this->getRequest(), $action);

        /* @var $ajax Mana_Ajax_Helper_Data */
        $ajax = Mage::helper('mana_ajax');

        $ajax->processPageWithoutRendering($this->_actionLayout['route'], $callback);
    }

    protected function _save() {
        throw new Mage_Core_Exception('Not implemented');
    }
}