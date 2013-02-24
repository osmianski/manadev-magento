<?php
/** 
 * @category    Mana
 * @package     ManaPro_Guestbook
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_Guestbook_Manapro_GuestbookController extends Mana_Admin_Controller_Crud  {
    protected function _getEntityName() {
        return 'manapro_guestbook/post';
    }
    protected function _registerModel() {
        $model = Mage::getModel($this->_getEntityName())->load($this->getRequest()->getParam('id'));
        Mage::register('m_crud_model', $model);
        return $model;
    }
    /**
     * Full page rendering action displaying list of entities of certain type.
     */
    public function indexAction() {
        // page
        $this->_title('Mana')->_title($this->__('Guest Book'));

        // layout
        $update = $this->getLayout()->getUpdate();
        $update->addHandle('default');
        $this->addActionLayoutHandles();
        $this->loadLayoutUpdates();
        $this->generateLayoutXml()->generateLayoutBlocks();
        $this->_isLayoutLoaded = true;
        $this->_initLayoutMessages('adminhtml/session');

        // rendering
        $this->_setActiveMenu('mana/guestbook');
        $this->renderLayout();
    }
    public function approveAction() {
        $ids = $this->getRequest()->getPost('ids');
        if (!empty($ids)) {
            Mage::getResourceModel('manapro_guestbook/post')->setStatuses($ids, ManaPro_Guestbook_Model_Post_Status::APPROVED);
        }
        $this->_redirect('*/*/');
    }
    public function rejectAction() {
        $ids = $this->getRequest()->getPost('ids');
        if (!empty($ids)) {
            Mage::getResourceModel('manapro_guestbook/post')->setStatuses($ids, ManaPro_Guestbook_Model_Post_Status::REJECTED);
        }
        $this->_redirect('*/*/');
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
    public function editAction() {
        $model = $this->_registerModel();

        // page
        if ($model->getId()) {
            $this->_title('Mana')->_title($this->__('Guest Post #%d', $model->getId()));
        }
        else {
            $this->_title('Mana')->_title($this->__('New Guest Post'));
        }

        // layout
        $update = $this->getLayout()->getUpdate();
        $update->addHandle('default');
        $this->addActionLayoutHandles();
        $this->loadLayoutUpdates();
        $this->generateLayoutXml()->generateLayoutBlocks();
        $this->_isLayoutLoaded = true;
        $this->_initLayoutMessages('adminhtml/session');

        // simplify if one tab
        if (($tabs = $this->getLayout()->getBlock('tabs')) && count($tabs->getChild()) == 1) {
            $content = $tabs->getActiveTabBlock();
            $tabs->getParentBlock()->unsetChild('tabs');
            $this->getLayout()->getBlock('container')->insert($content, $content->getNameInLayout(), null, $content->getNameInLayout());
            $content->addToParentGroup('content');
        }

        // rendering
        $this->_setActiveMenu('mana/guestbook');
        $this->renderLayout();

    }
    public function saveAction() {
   		// data
   		$fields = $this->getRequest()->getPost('fields');
        $useDefault = $this->getRequest()->getPost('use_default');
        $data = array();
        if ($this->getRequest()->getParam('id')) {
   		    $model = Mage::getModel('manapro_guestbook/post')->load($this->getRequest()->getParam('id'));
        }
        else {
            $model = Mage::getModel('manapro_guestbook/post');
        }

        $response = new Varien_Object();
        $update = array();
        /* @var $messages Mage_Adminhtml_Block_Messages */ $messages = $this->getLayout()->createBlock('adminhtml/messages');

        try {
            // processing
            $model->addEditedData($fields, $useDefault);
            $model->addEditedDetails($this->getRequest());
            $model->validateKeys();
            //Mage::helper('mana_db')->replicateObject($model, array(
            //    $model->getEntityName() => array('saved' => array($model->getId()))
            //));
            $model->validate();

            // do save
            $model->save();
            Mage::dispatchEvent('m_saved', array('object' => $model));
            $messages->addSuccess($this->__('Your changes are successfully saved.'));
            if (!$this->getRequest()->getParam('id')) {
                $response->setRefreshRedirect($this->getUrl('*/*/edit', array('id' => $model->getId())));
            }
        }
        catch (Mana_Db_Exception_Validation $e) {
            foreach ($e->getErrors() as $error) {
                $messages->addError($error);
            }
            $response->setError(true);
        }
        catch (Exception $e) {
            $messages->addError($e->getMessage());
            $response->setError(true);
        }

        $update[] = array('selector' => '#messages', 'html' => $messages->getGroupedHtml());
        $response->setUpdate($update);
        $this->getResponse()->setBody($response->toJson());
   	}
    public function newAction() {
        // the same form is used to create and edit
        $this->_forward('edit');
    }
    public function deleteAction() {
        try {
            if ($id = $this->getRequest()->getParam('id')) {
                $post = Mage::getModel('manapro_guestbook/post')->load($id);
                $post->delete();
                $this->_getSession()->addSuccess($this->__('The post has been deleted.'));
            }
            else {
                $ids = $this->getRequest()->getPost('ids');
                if (!empty($ids)) {
                    foreach ($ids as $id) {
                        $post = Mage::getModel('manapro_guestbook/post')->load($id);
                        $post->delete();
                    }
                    $this->_getSession()->addSuccess($this->__('The posts has been deleted.'));
                }
            }
        }
        catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }
}