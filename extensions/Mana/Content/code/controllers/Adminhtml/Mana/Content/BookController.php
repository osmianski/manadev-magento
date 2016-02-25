<?php
/** 
 * @category    Mana
 * @package     Mana_Content
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Content_Adminhtml_Mana_Content_BookController extends Mana_Admin_Controller_V2_Controller {

    public function newAction() {
        $this->getLayout()->getUpdate()->addHandle('adminhtml_mana_content_book_new');
        $this->_forward('edit');
    }

    public function editAction() {
        try {
            $models = $this->contentHelper()->registerModels($this->getRequest()->getParam('id'));
            Mage::dispatchEvent('m_load_related_products');
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('*/*/');
            return;
        }

        /* @var $model Mana_Content_Model_Page_Abstract */
        $model = $models['finalSettings'];

        // page
        if ($model->getId()) {
            $this->_title('Mana')->_title($this->__('%s - Book', $model->getData('title')));
        }
        else {
            $this->_title('Mana')->_title($this->__('New Book'));
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

        $this->addDataToClientSideBlock();
        // rendering
        $this->_setActiveMenu('mana/contentpage');
        $this->renderLayout();
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('admin/mana/contentpage');
    }

    public function saveAction() {
        $response = new Varien_Object();

        /* @var $messages Mage_Adminhtml_Block_Messages */
        $messages = $this->getLayout()->createBlock('adminhtml/messages');

        $changes = $this->getRequest()->getPost('changes');
        $changes = is_array($changes) ? $changes : array();
        $newId = array();
        $messagesPerRecord = array();

        $this->recoverContentField($changes);

        if($this->validateChangesObject($changes, $messagesPerRecord)) {
            foreach($changes as $action => $data) {
                foreach ($data as $id => $fields) {
                    $models = $this->contentHelper()->registerModels(($action == "created") ? null : $id, false);
                    $model = $models['customSettings'];
                    if(is_array($fields)) {
                        if (isset($fields['parent_id']['value']) && substr($fields['parent_id']['value'], 0, 1) <> "n") {
                            $fields['parent_id']['value'] = $model->getCustomSettingId($fields['parent_id']['value']);
                        }
                        if ($action == "created") {
                            if (isset($fields['id'])) {
                                $tmpId = $fields['id']['value'];
                                unset($fields['id']);
                            }
                        }
                        if (isset($fields['parent_id']['value']) && isset($newId[$fields['parent_id']['value']])) {
                            $fields['parent_id']['value'] = $newId[$fields['parent_id']['value']];
                        }
                    }
                    if($action != "deleted") {
                        // data
                        $this->_processChanges($model, $fields);
                    } else {
                        $model->delete();
                    }

                    if($action == "created") {
                        $newId[$id] = $model->getId();
                    }
                }
            }
            $messages->addSuccess($this->__('Your changes are successfully saved.'));
            $update['#messages'] = $messages->getGroupedHtml();
            foreach($newId as $tmpId => $customSettingId) {
                $newId[$tmpId] = $model->getGlobalId($customSettingId);
            }
            $response->setData('newId', $newId);
        } else {
            $errorPerRecord = array();
            $selectedRecord = $this->getRequest()->getPost('selectedRecord');
            foreach($messagesPerRecord as $id => $recordErrors) {
                $errorPerRecord[$id] = $recordErrors->getGroupedHtml();
                if($id == $selectedRecord) {
                    $messages = $recordErrors;
                }
            }
            $update['#messages'] = $messages->getGroupedHtml();

            $response->setData('errorPerRecord', $errorPerRecord);
            $response->setData('failed', true);
        }
        $response->setData('updates', $update);


        $refreshNewPage = ($this->getRequest()->getPost('rootPageId')) == "false";

        if ($refreshNewPage) {
            $response->setData(
                'forceEditUrl',
                $this->adminHelper()->getStoreUrl(
                '*/*/edit',
                    array(
                        'id' => reset($newId)
                    )
                )
            );
        }
        $this->getResponse()->setBody($response->toJson());
    }

    /**
     * @param $model Mana_Content_Model_Page_Abstract
     * @param $id int
     * @param $fields array
     */
    protected function _processChanges($model, $fields) {

        $this->contentHelper()->setModelData($model, $fields, true);

        // check if there are any custom settings
        $hasCustomSettings = false;
        foreach ($model->getData() as $key => $value) {
            if ($this->coreHelper()->startsWith($key, 'default_mask')) {
                if ($value) {
                    $hasCustomSettings = true;
                    break;
                }
            }
        }
        $model->setData('_has_custom_settings', $hasCustomSettings);

        // do save
        if ($this->adminHelper()->isGlobal()) {
            $model->save();
        } else {
            if ($model->getData('_has_custom_settings')) {
                $model->save();
            } elseif ($model->getId()) {
                $model->delete();
            }
        }
        $observerParam = array('object' => $model);
        if (isset($fields['related_products'])) {
            $observerParam['related_products'] = $fields['related_products'];
        }
        Mage::dispatchEvent('m_saved', $observerParam);
    }

    public function loadAction() {
        $changes = $this->getRequest()->getPost('changes');
        $changes = is_array($changes) ? $changes : array();
        $id = $this->getRequest()->getPost('id');
        if(substr($id, 0, 1) == "n") {
            $id = null;
        }
        $models = $this->contentHelper()->registerModels($id);
        $model = $models['finalSettings'];

        if(!is_null($changes)) {
            $this->recoverContentField($changes);
            if(!is_null($id)) {
                if(isset($changes['modified'])) {
                    foreach($changes['modified'] as $id => $field) {
                        if($model->getData('id') == $id || $model->getData('reference_id') == $id) {
                            if (isset($field['related_products'])) {
                                Mage::dispatchEvent('m_load_related_products', array('related_products' => $field['related_products']));
                            }
                            $this->contentHelper()->setModelData($model, $field);
                            Mage::unregister('m_flat_model');
                            Mage::register('m_flat_model', $model);
                            break;
                        }
                    }
                }
            } else {
                if (isset($changes['created'])) {
                    foreach($changes['created'] as $id => $field) {
                        if($this->getRequest()->getPost('id') == $id) {
                            if(isset($field['reference_id']) &&
                                $referenceId = $field['reference_id']['value']) {
                                if(isset($changes['modified'][$referenceId]) || isset($changes['created'][$referenceId])) {
                                    $originalPageChanges = (isset($changes['modified'][$referenceId])) ? $changes['modified'][$referenceId] : $changes['created'][$referenceId];
                                    unset($originalPageChanges['parent_id']);
                                    unset($originalPageChanges['position']);
                                    $field = array_merge($field, $originalPageChanges);
                                }
                            }
                            if(isset($field['related_products'])) {
                                Mage::dispatchEvent('m_load_related_products', array('related_products' => $field['related_products']));
                            }
                            $this->contentHelper()->setModelData($model, $field);
                            Mage::unregister('m_flat_model');
                            Mage::register('m_flat_model', $model);
                            break;
                        }
                    }
                }
            }
        }
        if(!Mage::registry('related_product_ids')) {
            Mage::dispatchEvent('m_load_related_products');
        }
        $this->loadLayout();
        if($model->getReferenceId()) {
            /** @var Mage_Adminhtml_Block_Messages $msgBlock */
            $msgBlock = $this->getLayout()->createBlock('adminhtml/messages', 'messages');
            $msgBlock->setBlockAlias('messages');
            $msgBlock->addNotice($this->contentHelper()->__("This is a reference page. As such, you can't make changes to this page. Instead, edit the original page by clicking `Go To Original Page` button above and it will load the original page. Changes to the original pages will be propagated to all their respective reference pages instantly."));
            $this->getLayout()->getBlock('container')->append($msgBlock);
            $msgBlock->addToParentGroup('top');
        }
        $this->addDataToClientSideBlock();

        // render AJAX result
        $this->renderLayout();
    }

    public function getRecordAction() {
        if($id = $this->getRequest()->getParam('id')) {
            $response = new Varien_Object();
            $dbHelper = $this->coreDbHelper();
            $models = $this->contentHelper()->registerModels($id, false);
            $model = $models['finalSettings'];
            $data = array();
            $columns = array(
                'is_active' => Mana_Content_Model_Page_Abstract::DM_IS_ACTIVE,
                'url_key' => Mana_Content_Model_Page_Abstract::DM_URL_KEY,
                'title' => Mana_Content_Model_Page_Abstract::DM_TITLE,
                'content' => Mana_Content_Model_Page_Abstract::DM_CONTENT,
                'page_layout' => Mana_Content_Model_Page_Abstract::DM_PAGE_LAYOUT,
                'layout_xml' => Mana_Content_Model_Page_Abstract::DM_LAYOUT_XML,
                'custom_layout_xml' => Mana_Content_Model_Page_Abstract::DM_CUSTOM_LAYOUT_XML,
                'custom_design_active_from' => Mana_Content_Model_Page_Abstract::DM_CUSTOM_DESIGN_ACTIVE_FROM,
                'custom_design_active_to' => Mana_Content_Model_Page_Abstract::DM_CUSTOM_DESIGN_ACTIVE_TO,
                'meta_title' => Mana_Content_Model_Page_Abstract::DM_META_TITLE,
                'meta_description' => Mana_Content_Model_Page_Abstract::DM_META_DESCRIPTION,
                'meta_keywords' => Mana_Content_Model_Page_Abstract::DM_META_KEYWORDS,
                'reference_id' => 0,
                'id' => 0,
                'default_mask0' => 0,
                'default_mask1' => 0,
            );
            foreach($model->getData() as $key => $value) {
                if(array_key_exists($key, $columns)) {
                    $data[$key] = array(
                        'value' => $value,
                        'isDefault' => $dbHelper->isModelContainsCustomSetting($model, $columns[$key])
                    );
                }
            }
            $response->setData('data', $data);
            $this->getResponse()->setBody($response->toJson());
        }
    }

    public function saveTreeStateAction() {
        $state = $this->getRequest()->getPost('state');
        if($state){
            Mage::getSingleton('admin/session')->setData('tree_state', $state);
        }
    }

    private function addDataToClientSideBlock() {
        $this->setDataToClientSideBlock('container',
            array(
                'tab_id' => $this->getLayout()->getBlock('tabs')->getId(),
            )
        );
    }

    protected function setDataToClientSideBlock($block, $array_values = array()) {
        $mBlockData = $this->getLayout()->getBlock($block)->getData('m_client_side_block');
        $mBlockData = array_merge($mBlockData, $array_values);
        $this->getLayout()->getBlock($block)->setData('m_client_side_block', $mBlockData);
    }

    private function validateChangesObject($changes, &$messagePerRecord) {
        try {
            foreach($changes as $action => $data) {
                if($action != "deleted") {
                    foreach($data as $id => $fields) {
                        $models = $this->contentHelper()->registerModels(($action == "created") ? null : $id, false);
                        /** @var Mana_Content_Model_Page_Abstract $model */
                        $model = $models['customSettings'];
                        $tmpId = $id;
                        unset($fields['parent_id']);
                        if($action == "modified") {
                            $model->load($id);
                        } elseif($action == "created") {
                            if(isset($fields['id'])) {
                                $tmpId = $fields['id']['value'];
                                unset($fields['id']);
                            }
                        }
                        $this->contentHelper()->setModelData($model, $fields);
                        $model->validate();
                        Mage::dispatchEvent('m_validate', array('object' => $model, 'fields' => $fields));
                    }
                }
            }
        } catch (Mana_Core_Exception_Validation $e) {
            foreach ($e->getErrors() as $error) {
                if(!isset($messagePerRecord[$id])) {
                    $messagePerRecord[$id] = $this->getLayout()->createBlock('adminhtml/messages');
                }
                $messagePerRecord[$id]->addError($error);
            }
        } catch (Exception $e) {
            $messagePerRecord[$id] = $this->getLayout()->createBlock('adminhtml/messages')->addError($e->getMessage());
        }
        foreach($messagePerRecord as $id => $messages) {
            if(count($messages) > 0) {
                return false;
            }
        }
        return true;
    }

    protected function recoverContentField(&$changes) {
        // Workaround for the Markdown plugin adding suffix to content field.
        // Content field reset in here instead of down below so that it will enter validation.
        foreach ($changes as $action => $data) {
            foreach ($data as $id => $fields) {
                if (is_array($fields)) {
                    foreach ($fields as $key => $value) {
                        if (substr($key, 0, 8) == "content_") {
                            $changes[$action][$id]['content'] = $value;
                            unset($changes[$action][$id][$key]);
                            break;
                        }
                    }
                }
            }
        }
    }

    #region Dependencies
    /**
     * @return Mana_Content_Helper_Data
     */
    public function contentHelper(){
        return Mage::helper('mana_content');
    }
    #endregion
}