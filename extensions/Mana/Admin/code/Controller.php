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
        /* @var $messages Mage_Adminhtml_Block_Messages */
        $messages = $this->getLayout()->createBlock('adminhtml/messages');

        try {
            $response = array();
            $this->_save($response);
            $messages->addSuccess($this->__('Your changes are successfully saved.'));
            if (!isset($response['updates'])) {
                $response['updates'] = array();
            }
            $response['updates']['#messages'] = $messages->getGroupedHtml();

            $this->getResponse()->setBody(json_encode($response));
        } catch (Mana_Db_Exception_Validation $e) {
            if (is_array($e->getErrors())) {
                foreach ($e->getErrors() as $error) {
                    $messages->addError($error);
                }
            }
            else {
                $messages->addError($e->getErrors());
            }
            $this->getResponse()->setBody(json_encode(array('error' => true, 'customErrorDisplay' => true,
                'updates' => array('#messages' => $messages->getGroupedHtml()))));
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

    protected function _save(&$response) {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper('mana_core');
        /* @var $ajax Mana_Ajax_Helper_Data */
        $ajax = Mage::helper('mana_ajax');

        /* @var $db Mana_Db_Helper_Data */
        $db = Mage::helper('mana_db');

        if (!($sessionId = $this->getRequest()->getParam('sessionId'))) {
            throw new Mage_Core_Exception($db->__('Page editing session is required but not provided.'));
        }
        if ($db->isEditingSessionExpired($this->getRequest()->getParam('sessionId'))) {
            throw new Mage_Core_Exception($db->__('Page editing session is expired. Please reload the page.'));
        }

        /* @var $pageDataSource Mana_Admin_Block_Data_Entity */
        $pageDataSource = $this->getLayout()->getBlock('page.data_source');

        $model = $pageDataSource->loadModel();

        $model->getResource()->beginTransaction();

        try {
            if (!$model->getId()) {
                $model->assignDefaultValues();
            }
            // add field data
            $model->disableIndexing()->validate($pageDataSource)->save();

            foreach ($pageDataSource->loadAdditionalModels() as $key => $additionalModel) {
                if (!$model->getId()) {
                    $additionalModel->assignDefaultValues();
                }
                // add field data
                $additionalModel->disableIndexing()->validate($pageDataSource)->save();
            }

            foreach ($pageDataSource->getChildDataSources() as $childDataSource) {
                /* @var $grid Mana_Admin_Block_Grid */
                $grid = $childDataSource->getParentBlock();
                /* @var $childDataSource Mana_Admin_Block_Data_Collection */
                $edit = $this->getRequest()->getParam($core->getBlockAlias($grid));

                $childDataSource
                    ->processPendingEdits($edit, $sessionId)
                    ->saveEditedData($edit, $sessionId, true);

                $grid->setEdit($edit);
                if (!isset($response['blocks'])) {
                    $response['blocks'] = array();
                }
                $response['blocks'][$grid->getNameInLayout()] = $ajax->renderBlock($grid->getNameInLayout());
            }

            $model->postValidate($pageDataSource);
            foreach ($pageDataSource->loadAdditionalModels() as $additionalModel) {
                $additionalModel->postValidate($pageDataSource);
            }
            foreach ($pageDataSource->getChildDataSources() as $childDataSource) {
                /* @var $childModel Mana_Db_Model_Entity */
                foreach ($childDataSource->createCollection()->setEditFilter(true) as $childModel) {
                    $childModel->postValidate($childDataSource);
                }
            }

            $model->updateIndexes();
            foreach ($pageDataSource->loadAdditionalModels() as $additionalModel) {
                $additionalModel->updateIndexes();
            }
            foreach ($pageDataSource->getChildDataSources() as $childDataSource) {
                /* @var $childModel Mana_Db_Model_Entity */
                foreach ($childDataSource->createCollection()->setEditFilter(true) as $childModel) {
                    $childModel->updateIndexes();
                }
            }
            $model->getResource()->commit();
        } catch (Exception $e) {
            $model->getResource()->rollBack();
            throw $e;
        }

    }
}