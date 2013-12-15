<?php
/** 
 * @category    Mana
 * @package     Mana_AttributePage
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_AttributePage_Adminhtml_Mana_AttributePageController extends Mana_Admin_Controller_V2_Controller  {
    protected function _registerModels() {
        if (!($customSettings = Mage::registry('m_edit_model'))) {
            if ($this->adminHelper()->isGlobal()) {
                $customSettings = Mage::getModel('mana_attributepage/attributePage_globalCustomSettings');
                $finalSettings = Mage::getModel('mana_attributepage/attributePage_global');
                $finalSettings->setData('_add_option_page_defaults', true);
                if ($id = $this->getRequest()->getParam('id')) {
                    $finalSettings->load($id);
                    if (!$finalSettings->getId()) {
                        throw new Mage_Core_Exception($this->__('This attribute page no longer exists.'));
                    }
                    $customSettings->load($finalSettings->getData('attribute_page_global_custom_settings_id'));
                }
                else {
                    $finalSettings->setDefaults();
                    $customSettings->setData('_add_option_page_defaults', true);
                    $customSettings->setDefaults();
                }
            }
            else {
                if ($id = $this->getRequest()->getParam('id')) {
                    $customSettings = Mage::getModel('mana_attributepage/attributePage_storeCustomSettings');
                    $finalSettings = Mage::getModel('mana_attributepage/attributePage_store');
                    $finalSettings->setData('store_id', $this->adminHelper()->getStore()->getId());
                    $finalSettings->load($id, 'attribute_page_global_id');
                    if (!$finalSettings->getId()) {
                        throw new Mage_Core_Exception($this->__('This attribute page no longer exists.'));
                    }

                    $customGlobalSettings = Mage::getModel('mana_attributepage/attributePage_globalCustomSettings');
                    $finalGlobalSettings = Mage::getModel('mana_attributepage/attributePage_global');
                    $finalGlobalSettings->setData('_add_option_page_defaults', true);
                    $finalGlobalSettings->load($id);
                    $customGlobalSettings->load($finalGlobalSettings->getData('attribute_page_global_custom_settings_id'));
                    Mage::register('m_global_edit_model', $customGlobalSettings);
                    Mage::register('m_global_flat_model', $finalGlobalSettings);

                    if ($customSettingsId = $finalSettings->getData('attribute_page_store_custom_settings_id')) {
                        $customSettings->load($customSettingsId);
                    }
                    else {
                        $customSettings
                            ->setData('store_id', $this->adminHelper()->getStore()->getId())
                            ->setData('attribute_page_global_id', $finalGlobalSettings->getId());
                    }
                }
                else {
                    throw new Mage_Core_Exception($this->__('Non existent attribute pages can not be customized on store level.'));
                }
            }
            Mage::register('m_edit_model', $customSettings);
            Mage::register('m_flat_model', $finalSettings);
        }
        else {
            $finalSettings = Mage::registry('m_flat_model');
        }

        return compact('customSettings', 'finalSettings');
    }

    public function indexAction() {
        // page
        $this->_title('Mana')->_title($this->__('Attribute Pages'));

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
        $this->_setActiveMenu('mana/attributepage');
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
            $models = $this->_registerModels();
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('*/*/');
            return;
        }

        /* @var $model Mana_AttributePage_Model_AttributePage_Abstract */
        $model = $models['finalSettings'];

        // page
        if ($model->getId()) {
            $this->_title('Mana')->_title($this->__('%s - Attribute Page', $model->getData('title')));
        }
        else {
            $this->_title('Mana')->_title($this->__('New Attribute Page'));
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

        // simplify if one tab
        if (($tabs = $this->getLayout()->getBlock('tabs')) && count($tabs->getChild()) == 1) {
            /* @var $tabs Mana_Admin_Block_V2_Tabs */
            $content = $tabs->getActiveTabBlock();
            $tabs->getParentBlock()->unsetChild('tabs');
            $this->getLayout()->getBlock('container')->insert($content, $content->getNameInLayout(), null, $content->getNameInLayout());
            $content->addToParentGroup('content');
        }

        // rendering
        $this->_setActiveMenu('mana/attributepage');
        $this->renderLayout();
     }

    public function saveAction() {
        // data
        $models = $this->_registerModels();
        $response = new Varien_Object();

        /* @var $messages Mage_Adminhtml_Block_Messages */
        $messages = $this->getLayout()->createBlock('adminhtml/messages');

        /* @var $model Mana_AttributePage_Model_AttributePage_Abstract */
        $model = $models['customSettings'];

        $refreshNewPage = $this->adminHelper()->isGlobal() && !$model->getId();
        try {
            $this->_processChanges();

            // do save
            if ($this->adminHelper()->isGlobal()) {
                $model->save();
            }
            else {
                if ($model->getData('_has_custom_settings')) {
                    $model->save();
                }
                elseif ($model->getId()) {
                    $model->delete();
                }
            }
            Mage::dispatchEvent('m_saved', array('object' => $model));
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
                '*/*/edit', array('id' => $model->getFinalId()
            )));
        }
        $this->getResponse()->setBody($response->toJson());
    }

    protected function _processChanges() {
        // data
        $models = $this->_registerModels();

        /* @var $model Mana_AttributePage_Model_AttributePage_Abstract */
        $model = $models['customSettings'];

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

        // implode multiple select fields
        foreach (array('option_page_available_sort_by') as $key) {
            if ($model->hasData($key) && is_array($model->getData($key))) {
                $model->setData($key, implode(',', $model->getData($key)));
            }
        }

        // process nullable fields
        foreach (array('attribute_id_1', 'attribute_id_2', 'attribute_id_3', 'attribute_id_4',
            'custom_design_active_from', 'custom_design_active_to', 'option_page_price_step',
            'option_page_custom_design_active_from', 'option_page_custom_design_active_to') as $key)
        {
            if ($model->hasData($key) && !trim($model->getData($key))) {
                $model->setData($key, null);
            }
        }
        // validate if all required data is entered and makes sense
        $model->validate();
    }

    public function deleteAction() {
        if (!$this->adminHelper()->isGlobal()) {
            $this->getSessionSingleton()->addError("Attribute page can only be deleted globally");
            $this->_redirect('*/*/');
            return;
        }

        try {
            $models = $this->_registerModels();
            /* @var $model Mana_AttributePage_Model_AttributePage_Abstract */
            $model = $models['finalSettings'];
            $model->delete();

            $model = $models['customSettings'];
            $model->delete();
            $this->getSessionSingleton()->addSuccess($this->__('Attribute page and all related option pages are deleted successfully!'));
        }
        catch (Exception $e) {
            $this->getSessionSingleton()->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }
}