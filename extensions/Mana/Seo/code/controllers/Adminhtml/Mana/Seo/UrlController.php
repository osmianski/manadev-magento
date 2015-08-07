<?php
/** 
 * @category    Mana
 * @package     Mana_Seo
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Seo_Adminhtml_Mana_Seo_UrlController extends Mana_Admin_Controller_V2_Controller {
    /**
     * @return Mana_Seo_Model_Url
     */
    protected function _registerModel() {
        if (!($model = Mage::registry('m_edit_model'))) {
            $model = $this->dbHelper()->getModel('mana_seo/url');
            $model->load($this->getRequest()->getParam('id'));

            Mage::register('m_edit_model', $model);
            Mage::register('m_flat_model', $model);
        }
        return $model;
    }

    protected function _processChanges() {
        // data
        $model = $this->_registerModel();

        // processing
        if ($fields = $this->getRequest()->getPost('fields')) {
            foreach ($fields as $key => $value) {
                $model->setData($key, $value);
            }
        }
    }

    public function indexAction() {
        // page
        $this->_title('MANAdev')->_title($this->__('SEO URL Keys'));

        // layout
        $update = $this->getLayout()->getUpdate();
        $update->addHandle('default');
        $this->addActionLayoutHandles();

        $this->loadLayoutUpdates();
        $this->generateLayoutXml()->generateLayoutBlocks();
        $this->_isLayoutLoaded = true;
        $this->_initLayoutMessages('adminhtml/session');

        // rendering
        $this->_setActiveMenu('mana/seo_url');
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

    public function editAction() {
        $model = $this->_registerModel();

        // page
        $this->_title('Mana')->_title($this->__('%s - SEO URL Key', $model->getFinalUrlKey()));

        // layout
        $update = $this->getLayout()->getUpdate();
        $update->addHandle('default');
        $this->addActionLayoutHandles();

        $this->loadLayoutUpdates();
        $this->generateLayoutXml()->generateLayoutBlocks();
        $this->_isLayoutLoaded = true;
        $this->_initLayoutMessages('adminhtml/session');

        // rendering
        $this->_setActiveMenu('mana/seo_url');
        $this->renderLayout();
    }

    public function saveAction() {
        // data
        $model = $this->_registerModel();
        $response = new Varien_Object();

        /* @var $messages Mage_Adminhtml_Block_Messages */
        $messages = $this->getLayout()->createBlock('adminhtml/messages');

        try {
            $this->_processChanges();

            // do save
            $model->save();
            Mage::dispatchEvent('m_saved', array('object' => $model));
            $messages->addSuccess($this->__('Your changes are successfully saved.'));
        } catch (Mana_Db_Exception_Validation $e) {
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
        $this->getResponse()->setBody($response->toJson());
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('admin/mana/seo_url');
    }

    #region Dependencies
    /**
     * @return Mana_Seo_Helper_Data
     */
    public function seoHelper() {
        return Mage::helper('mana_seo');
    }

    #endregion
}