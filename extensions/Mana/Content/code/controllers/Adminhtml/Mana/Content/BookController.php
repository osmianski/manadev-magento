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
    protected function _registerModels($id = null, $saveToRegistry = true) {
        if (!($customSettings = Mage::registry('m_edit_model'))) {
            if ($this->adminHelper()->isGlobal()) {
                /* @var $customSettings Mana_Content_Model_Page_GlobalCustomSettings */            
                $customSettings = Mage::getModel('mana_content/page_globalCustomSettings');

                /* @var $finalSettings Mana_Content_Model_Page_Global */
                $finalSettings = Mage::getModel('mana_content/page_global');

                if (!is_null($id)) {
                    $finalSettings->load($id);
                    if (!$finalSettings->getId()) {
                        throw new Mage_Core_Exception($this->__('This page no longer exists.'));
                    }
                    $customSettings->load($finalSettings->getData('page_global_custom_settings_id'));
                    $customSettings->setData('page_global_id', $finalSettings->getId());
                }
                else {
                    $finalSettings->setDefaults();
                    $customSettings->setDefaults();
                }
            }
            else {
                if (!is_null($id)) {
                    /* @var $customSettings Mana_Content_Model_Page_StoreCustomSettings */
                    $customSettings = Mage::getModel('mana_content/page_storeCustomSettings');

                    /* @var $finalSettings Mana_Content_Model_Page_Store */
                    $finalSettings = Mage::getModel('mana_content/page_store');

                    $finalSettings->setData('store_id', $this->adminHelper()->getStore()->getId());
                    $finalSettings->setData("_load_global_custom_settings_id", true);
                    $finalSettings->load($id, 'page_global_id');

                    if (!$finalSettings->getId()) {
                        throw new Mage_Core_Exception($this->__('This page no longer exists.'));
                    }

                    /* @var $customGlobalSettings Mana_Content_Model_Page_GlobalCustomSettings */
                    $customGlobalSettings = Mage::getModel('mana_content/page_globalCustomSettings');

                    /* @var $finalGlobalSettings Mana_Content_Model_Page_Global */
                    $finalGlobalSettings = Mage::getModel('mana_content/page_global');
                    $finalGlobalSettings->load($id);
                    $customGlobalSettings->load($finalGlobalSettings->getData('page_global_custom_settings_id'));

                    if($saveToRegistry) {
                        Mage::register('m_global_edit_model', $customGlobalSettings);
                        Mage::register('m_global_flat_model', $finalGlobalSettings);
                    }

                    if ($customSettingsId = $finalSettings->getData('page_store_custom_settings_id')) {
                        $customSettings->load($customSettingsId);
                    }
                    else {
                        $customSettings
                            ->setData('store_id', $this->adminHelper()->getStore()->getId())
                            ->setData('page_global_id', $finalGlobalSettings->getId());
                    }
                }
                else {
                    throw new Mage_Core_Exception($this->__('Non existent pages can not be customized on store level.'));
                }
            }
            if($saveToRegistry) {
                Mage::register('m_edit_model', $customSettings);
                Mage::register('m_flat_model', $finalSettings);
            }
        }
        else {
            $finalSettings = Mage::registry('m_flat_model');
        }

        return compact('customSettings', 'finalSettings');
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function editAction() {
        Mage::register('cms_page', Mage::getModel('cms/page')->load('home', 'identifier'));
        try {
            $models = $this->_registerModels($this->getRequest()->getParam('id'));
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

    public function saveAction() {
        $response = new Varien_Object();

        /* @var $messages Mage_Adminhtml_Block_Messages */
        $messages = $this->getLayout()->createBlock('adminhtml/messages');

        $changes = $this->getRequest()->getPost('changes');
        $newId = array();
        $messagesPerRecord = array();

        // Workaround for the Markdown plugin adding suffix to content field.
        // Content field reset in here instead of down below so that it will enter validation.
        foreach($changes as $action => $data) {
            foreach($data as $id => $fields) {
                if(is_array($fields)) {
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

        if($this->validateChangesObject($changes, $messagesPerRecord)) {
            foreach($changes as $action => $data) {
                foreach ($data as $id => $fields) {
                    $models = $this->_registerModels(($action == "created") ? null : $id, false);
                    $model = $models['customSettings'];
                    if(isset($fields['parent_id']) && substr( $fields['parent_id']['value'], 0, 1) <> "n") {
                        $fields['parent_id']['value'] = $model->getCustomSettingId($fields['parent_id']['value']);
                    }

                    if($action == "created") {
                        if (isset($fields['id'])) {
                            $tmpId = $fields['id']['value'];
                            unset($fields['id']);
                        }
                        if (isset($fields['parent_id']['value']) && isset($newId[$fields['parent_id']['value']])) {
                            $fields['parent_id']['value'] = $newId[$fields['parent_id']['value']];
                        }
                    } elseif($action == "modified" || $action == "deleted") {
                        $model->load($id);
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
            $messages->addError("There are validation errors.");

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

        $this->setModelData($model, $fields);

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
        Mage::dispatchEvent('m_saved', array('object' => $model));
    }

    public function loadAction() {
        $changes = $this->getRequest()->getPost('changes');
        $id = $this->getRequest()->getPost('id');
        if(substr($id, 0, 1) == "n") {
            $id = null;
        }
        $models = $this->_registerModels($id);
        $model = $models['finalSettings'];

        if(!is_null($id)) {
            foreach($changes['modified'] as $id => $field) {
                if($model->getData('page_global_custom_settings_id') == $id) {
                    foreach($field as $fieldName => $fieldData) {
                        $model->setData($fieldName, $fieldData['value']);
                    }
                    Mage::unregister('m_flat_model');
                    Mage::register('m_flat_model', $model);
                    break;
                }
            }
        } else {
            foreach($changes['created'] as $id => $field) {
                if($this->getRequest()->getPost('id') == $id) {
                    foreach($field as $fieldName => $fieldData) {
                        $model->setData($fieldName, $fieldData['value']);
                    }
                    Mage::unregister('m_flat_model');
                    Mage::register('m_flat_model', $model);
                    break;
                }
            }
        }
//        $this->addActionLayoutHandles();
//        $this->loadLayoutUpdates();
//        $this->generateLayoutXml()->generateLayoutBlocks();
//        $this->_isLayoutLoaded = true;
        $this->loadLayout();
        $this->addDataToClientSideBlock();

        // render AJAX result
        $this->renderLayout();
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
        foreach($changes as $action => $data) {
            if($action != "deleted") {
                foreach($data as $id => $fields) {
                    try {
                        $models = $this->_registerModels(($action == "created") ? null : $id, false);
                        $model = $models['customSettings'];
                        $tmpId = $id;
                        if($action == "modified") {
                            $model->load($id);
                        } elseif($action == "created") {
                            if(isset($fields['id'])) {
                                $tmpId = $fields['id']['value'];
                                unset($fields['id']);
                            }
                        }
                        $this->setModelData($model, $fields);
                        $model->validate();
                    } catch (Mana_Core_Exception_Validation $e) {
                        foreach ($e->getErrors() as $error) {
                            if(!$messagePerRecord[$id]) {
                                $messagePerRecord[$id] = $this->getLayout()->createBlock('adminhtml/messages');
                            }
                            $messagePerRecord[$id]->addError($error);
                        }
                    } catch (Exception $e) {
                        $messagePerRecord[$id] = $this->getLayout()->createBlock('adminhtml/messages')->addError($e->getMessage());
                    }
                }
            }
        }
        foreach($messagePerRecord as $id => $messages) {
            if(count($messages) > 0) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param $model
     * @param $fields
     */
    protected function setModelData($model, $fields) {
        foreach ($fields as $field => $fieldData) {
            $model->setData($field, $fieldData['value']);
            $this->coreDbHelper()->isModelContainsCustomSetting($model, $field, !($fieldData['isDefault'] === "true"));
        }
    }
}