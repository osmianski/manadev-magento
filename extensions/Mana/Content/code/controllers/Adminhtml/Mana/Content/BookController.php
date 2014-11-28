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
        $this->_forward('edit');
    }

    public function editAction() {
        Mage::register('cms_page', Mage::getModel('cms/page')->load('home', 'identifier'));
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
                    $models = $this->contentHelper()->registerModels(($action == "created") ? null : $id, false);
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

        $this->contentHelper()->setModelData($model, $fields);

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
        $id = $this->getRequest()->getPost('id');
        if(substr($id, 0, 1) == "n") {
            $id = null;
        }
        $models = $this->contentHelper()->registerModels($id);
        $model = $models['finalSettings'];

        if(!is_null($changes)) {
            if(!is_null($id)) {
                foreach($changes['modified'] as $id => $field) {
                    if($model->getData('id') == $id || $model->getData('reference_id') == $id) {
                        if (isset($field['related_products'])) {
                            Mage::dispatchEvent('m_load_related_products', array('related_products' => $field['related_products']));
                        }
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
                        if($referenceId = $field['reference_id']['value']) {
                            $originalPageChanges = ($changes['modified'][$referenceId]) ? $changes['modified'][$referenceId] : $changes['created'][$referenceId];
                            unset($originalPageChanges['parent_id']);
                            unset($originalPageChanges['position']);
                            $field = array_merge($field, $originalPageChanges);
                        }
                        if(isset($field['related_products'])) {
                            Mage::dispatchEvent('m_load_related_products', array('related_products' => $field['related_products']));
                        }
                        foreach($field as $fieldName => $fieldData) {
                            $model->setData($fieldName, $fieldData['value']);
                        }
                        Mage::unregister('m_flat_model');
                        Mage::register('m_flat_model', $model);
                        break;
                    }
                }
            }
        }
        if(!Mage::registry('related_product_ids')) {
            Mage::dispatchEvent('m_load_related_products');
        }
        $this->loadLayout();
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
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $connection->beginTransaction();
        foreach($changes['deleted'] as $id => $fields) {
            $models = $this->contentHelper()->registerModels($id, false);
            $model = $models['customSettings'];
            $model->delete();
        }
        foreach($changes['modified'] as $id => $fields) {
            $models = $this->contentHelper()->registerModels($id, false);
            $model = $models['customSettings'];
            $this->contentHelper()->setModelData($model, $fields);
            $model->save();
        }

        try {
            $url_keys = array();
            foreach ($changes['created'] as $id => $fields) {
                if (in_array($fields['url_key']['value'], $url_keys)) {
                    $message = Mage::getModel('mana_content/page_global')->getValidator()->getMessage('url_key', 'unique');
                    throw new Exception($message);
                } else {
                    array_push($url_keys, $fields['url_key']['value']);
                }
            }
            foreach($changes as $action => $data) {
                if($action != "deleted") {
                    foreach($data as $id => $fields) {
                            $models = $this->contentHelper()->registerModels(($action == "created") ? null : $id, false);
                            /** @var Mana_Content_Model_Page_Abstract $model */
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
                            $this->contentHelper()->setModelData($model, $fields);
                            if($model->getReferenceId()) {
                                $model->getValidator()->ignoreRule('unique');
                            }
                            $model->validate();
                            Mage::dispatchEvent('m_validate', array('object' => $model, 'fields' => $fields));
                    }
                }
            }
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
        $connection->rollback();
        foreach($messagePerRecord as $id => $messages) {
            if(count($messages) > 0) {
                return false;
            }
        }
        return true;
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