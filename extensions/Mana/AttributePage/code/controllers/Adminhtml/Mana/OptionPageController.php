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
class Mana_AttributePage_Adminhtml_Mana_OptionPageController extends Mana_Admin_Controller_V2_Controller  {
    /**
     * @return Mana_AttributePage_Model_AttributePage_Abstract
     * @throws Mage_Core_Exception
     */
    protected function _registerAttributePage() {
        if (!($finalSettings = Mage::registry('m_attribute_page'))) {
            if ($id = $this->getRequest()->getParam('parent_id')) {
                if ($this->adminHelper()->isGlobal()) {
                    $finalSettings = Mage::getModel('mana_attributepage/attributePage_global');
                    $finalSettings->setData('_add_option_page_defaults', true);
                    $finalSettings->load($id);
                    if (!$finalSettings->getId()) {
                        throw new Mage_Core_Exception($this->__('This attribute page no longer exists.'));
                    }
                }
                else {
                    $finalSettings = Mage::getModel('mana_attributepage/attributePage_store');
                    $finalSettings->setData('store_id', $this->adminHelper()->getStore()->getId());
                    $finalSettings->load($id, 'attribute_page_global_id');
                    if (!$finalSettings->getId()) {
                        throw new Mage_Core_Exception($this->__('This attribute page no longer exists.'));
                    }

                    $finalGlobalSettings = Mage::getModel('mana_attributepage/attributePage_global');
                    $finalGlobalSettings->setData('_add_option_page_defaults', true);
                    $finalGlobalSettings->load($id);
                    Mage::register('m_global_attribute_page', $finalGlobalSettings);
                }
            }
            else {
                throw new Mage_Core_Exception($this->__('Attribute page id is not specified.'));
            }

            Mage::register('m_attribute_page', $finalSettings);
        }
        return $finalSettings;
    }

    /**
     * @param Mana_AttributePage_Model_AttributePage_Abstract $attributePage
     * @return array
     * @throws Mage_Core_Exception
     */
    protected function _registerModels($attributePage) {
        if (!($customSettings = Mage::registry('m_edit_model'))) {
            if ($id = $this->getRequest()->getParam('id')) {
                if ($this->adminHelper()->isGlobal()) {
                    $customSettings = Mage::getModel('mana_attributepage/optionPage_globalCustomSettings');
                    $finalSettings = Mage::getModel('mana_attributepage/optionPage_global');
                    $finalSettings->load($id);
                    if (!$finalSettings->getId()) {
                        throw new Mage_Core_Exception($this->__('This option page no longer exists.'));
                    }
                    if ($customSettingsId = $finalSettings->getData('option_page_global_custom_settings_id')) {
                        $customSettings->load($customSettingsId);
                    }
                    else {
                        $customSettings
                            ->setData('attribute_page_global_id', $attributePage->getId())
                            ->setData('option_id_0', $finalSettings->getData('option_id_0'))
                            ->setData('option_id_1', $finalSettings->getData('option_id_1'))
                            ->setData('option_id_2', $finalSettings->getData('option_id_2'))
                            ->setData('option_id_3', $finalSettings->getData('option_id_3'))
                            ->setData('option_id_4', $finalSettings->getData('option_id_4'));
                    }
                }
                else {
                    $customSettings = Mage::getModel('mana_attributepage/optionPage_storeCustomSettings');
                    $finalSettings = Mage::getModel('mana_attributepage/optionPage_store');
                    $finalSettings->setData('store_id', $this->adminHelper()->getStore()->getId());
                    $finalSettings->load($id, 'option_page_global_id');
                    if (!$finalSettings->getId()) {
                        throw new Mage_Core_Exception($this->__('This option page no longer exists.'));
                    }

                    $customGlobalSettings = Mage::getModel('mana_attributepage/optionPage_globalCustomSettings');
                    $finalGlobalSettings = Mage::getModel('mana_attributepage/optionPage_global');
                    $finalGlobalSettings->load($id);
                    $customGlobalSettings->load($finalGlobalSettings->getData('option_page_global_custom_settings_id'));
                    Mage::register('m_global_edit_model', $customGlobalSettings);
                    Mage::register('m_global_flat_model', $finalGlobalSettings);

                    if ($customSettingsId = $finalSettings->getData('option_page_store_custom_settings_id')) {
                        $customSettings->load($customSettingsId);
                    }
                    else {
                        $customSettings
                            ->setData('store_id', $this->adminHelper()->getStore()->getId())
                            ->setData('option_page_global_id', $finalGlobalSettings->getId());
                    }
                }
                Mage::register('m_edit_model', $customSettings);
                Mage::register('m_flat_model', $finalSettings);

            }
            else {
                throw new Mage_Core_Exception($this->__('Option page id is not specified.'));
            }
        }
        else {
            $finalSettings = Mage::registry('m_flat_model');
        }

        return compact('customSettings', 'finalSettings');
    }

    public function indexAction() {
        try {
            $attributePage = $this->_registerAttributePage();
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('*/mana_attributePage/');
            return;
        }

        // page
        $this->_title('Mana')->_title($this->__('%s Option Pages', $attributePage->getData('title')));

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
        $this->_registerAttributePage();

        // layout
        $this->addActionLayoutHandles();
        $this->loadLayoutUpdates();
        $this->generateLayoutXml()->generateLayoutBlocks();
        $this->_isLayoutLoaded = true;

        // render AJAX result
        $this->renderLayout();
    }

    public function editAction() {
        try {
            $attributePage = $this->_registerAttributePage();
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('*/mana_attributePage/');
            return;
        }
        try {
            $models = $this->_registerModels($attributePage);
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('*/*/');
            return;
        }

        /* @var $model Mana_AttributePage_Model_OptionPage_Abstract */
        $model = $models['finalSettings'];

        // page
        $this->_title('Mana')->_title($this->__('%s - Option Page', $model->getData('title')));

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
        $attributePage = $this->_registerAttributePage();
        $models = $this->_registerModels($attributePage);
        $response = new Varien_Object();

        /* @var $messages Mage_Adminhtml_Block_Messages */
        $messages = $this->getLayout()->createBlock('adminhtml/messages');

        /* @var $model Mana_AttributePage_Model_OptionPage_Abstract */
        $model = $models['customSettings'];

        try {
            $this->_processChanges();

            // do save
            if ($model->getData('_has_custom_settings')) {
                $model->save();
            }
            elseif ($model->getId()) {
                $model->delete();
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
        $this->getResponse()->setBody($response->toJson());
    }

    protected function _processChanges() {
        // data
        $attributePage = $this->_registerAttributePage();
        $models = $this->_registerModels($attributePage);

        /* @var $model Mana_AttributePage_Model_OptionPage_Abstract */
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
        foreach (array('available_sort_by') as $key) {
            if ($model->hasData($key) && is_array($model->getData($key))) {
                $model->setData($key, implode(',', $model->getData($key)));
            }
        }

        // process nullable fields
        foreach (array('option_id_1', 'option_id_2', 'option_id_3', 'option_id_4',
            'custom_design_active_from', 'custom_design_active_to', 'price_step') as $key)
        {
            if ($model->hasData($key) && !trim($model->getData($key))) {
                $model->setData($key, null);
            }
        }

        // process image fields
        foreach (array('image') as $key) {
            if ($this->coreDbHelper()->isModelContainsCustomSetting($model, $key) &&
                ($relativeUrl = $model->getData($key)))
            {
                if ($sourcePath = $this->fileHelper()->getFilename($relativeUrl, 'temp/image')) {
                    $targetPath = $this->fileHelper()->getFilename($relativeUrl, 'image', true);
                    if (file_exists($targetPath)) {
                        unlink($targetPath);
                    }
                    copy($sourcePath, $targetPath);
                    unlink($sourcePath);
                }

            }
        }

        // validate if all required data is entered and makes sense
        $model->validate();
    }
}