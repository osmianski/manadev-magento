<?php
/** 
 * @category    Mana
 * @package     Mana_Page
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Page_Adminhtml_Mana_SpecialController extends Mana_Admin_Controller_V2_Controller  {
    public function indexAction() {
        // page
        $this->_title('Mana')->_title($this->__('Special Filters, Pages and Tags'));

        // layout
        $update = $this->getLayout()->getUpdate();
        $update->addHandle('default');
        $this->addActionLayoutHandles();

        if (!Mage::app()->isSingleStoreMode()) {
            $update->addHandle('mana_admin2_multistore_list');
        }
        $this->loadLayoutUpdates();
        $this->generateLayoutXml()->generateLayoutBlocks();
        $this->_isLayoutLoaded = true;
        $this->_initLayoutMessages('adminhtml/session');

        // rendering
        $this->_setActiveMenu('mana/special');
        $this->renderLayout();
    }

    public function gridAction() {
        // layout
        $this->addActionLayoutHandles();
        $this->loadLayoutUpdates();
        $this->generateLayoutXml()->generateLayoutBlocks();
        $this->_isLayoutLoaded = true;

        // render AJAX result
        $this->renderLayout();
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function editAction() {
        try {
            $model = $this->_registerModel();
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('*/*/');
            return;
        }

        // page
        if ($model->getId()) {
            $this->_title('Mana')->_title($this->__('%s - Special Filter, Page or Tag', $model->getData('title')));
        }
        else {
            $this->_title('Mana')->_title($this->__('New Special Filter, Page or Tag'));
        }

        // layout
        $update = $this->getLayout()->getUpdate();
        $update->addHandle('default');
        $this->addActionLayoutHandles();

        if ($model->getId() && !Mage::app()->isSingleStoreMode()) {
            $update->addHandle('mana_admin2_multistore_card');
        }
        $this->loadLayoutUpdates();
        $this->generateLayoutXml()->generateLayoutBlocks();
        $this->_isLayoutLoaded = true;
        $this->_initLayoutMessages('adminhtml/session');

        // rendering
        $this->_setActiveMenu('mana/special');
        $this->renderLayout();
    }

    public function saveAction() {
        $model = $this->_registerModel();

       /* @var $resource Mana_Page_Resource_Special */
        $resource = Mage::getResourceSingleton('mana_page/special');

        $response = new Varien_Object();

        /* @var $messages Mage_Adminhtml_Block_Messages */
        $messages = $this->getLayout()->createBlock('adminhtml/messages');

        $refreshNewPage = $this->adminHelper()->isGlobal() && !$model->getId();

        try {
            $this->_processChanges();
            $resource->saveModel($model);
            $messages->addSuccess($this->__('Your changes are successfully saved.'));
        } catch (Mana_Core_Exception_Validation $e) {
            foreach ($e->getErrors() as $error) {
                $messages->addError($error);
            }
            $response->setData('failed', true);
        }
        catch (Exception $e) {
            $messages->addError($e->getMessage());
            $response->setData('failed', true);
        }

        $update['#messages'] = $messages->getGroupedHtml();
        $response->setData('updates', $update);
        if ($refreshNewPage) {
            $response->setData('forceEditUrl', $this->adminHelper()->getStoreUrl(
                '*/*/edit', array('id' => $model->getId()
            )));
        }
        $this->getResponse()->setBody($response->toJson());
    }

    public function deleteAction() {
        /* @var $resource Mana_Page_Resource_Special */
        $resource = Mage::getResourceSingleton('mana_page/special');

        if (!$this->adminHelper()->isGlobal()) {
            $this->getSessionSingleton()->addError("Special condition can only be deleted globally");
            $this->_redirect('*/*/');
            return;
        }

        try {
            $resource->delete($this->getRequest()->getParam('id'));
            $this->getSessionSingleton()->addSuccess($this->__('Special condition is deleted successfully!'));
        }
        catch (Exception $e) {
            $this->getSessionSingleton()->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }

    /**
     * @return Mana_Page_Model_Special
     * @throws Mage_Core_Exception
     */
    protected function _registerModel() {
        /* @var $resource Mana_Page_Resource_Special */
        $resource = Mage::getResourceSingleton('mana_page/special');

        if (!($model = Mage::registry('m_edit_model'))) {
            if ($id = $this->getRequest()->getParam('id')) {
                if (!($model = $resource->getModel($id, $this->adminHelper()->getStore()->getId()))) {
                    throw new Mage_Core_Exception($this->__('This special condition no longer exists.'));
                }
                if (!$this->adminHelper()->isGlobal()) {
                    if (!($globalModel = $resource->getModel($id, 0))) {
                        throw new Mage_Core_Exception($this->__('This special condition no longer exists on global level.'));
                    }
                    Mage::register('m_global_edit_model', $globalModel);
                    Mage::register('m_global_flat_model', $globalModel);
                }
            }
            else {
                if ($this->adminHelper()->isGlobal()) {
                    $model = Mage::getModel('mana_page/special');
                }
                else {
                    throw new Mage_Core_Exception($this->__('Non existent special condition can not be customized on store level.'));
                }

            }
            Mage::register('m_edit_model', $model);
            Mage::register('m_flat_model', $model);
        }
        return $model;
    }

    protected function _processChanges() {
        $model = $this->_registerModel();

        // process custom settings
        if ($fields = $this->getRequest()->getPost('fields')) {
            foreach ($fields as $key => $value) {
                $this->coreDbHelper()->isModelContainsCustomSetting($model, $key, true);
                $model->setData($key, $value);
            }
        }

        // process settings which uses default values
        if ($useDefault = $this->getRequest()->getPost('use_default')) {
            foreach ($useDefault as $key) {
                $this->coreDbHelper()->isModelContainsCustomSetting($model, $key, false);
            }
        }

        // validate if all required data is entered and makes sense
        $model->validate();
    }
}