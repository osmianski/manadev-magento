<?php
/** 
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */


class Mana_Sorting_Adminhtml_Mana_Sorting_MethodController extends Mana_Admin_Controller_V2_Controller{

    protected function _registerModels() {
        if (!($customSettings = Mage::registry('m_edit_model'))) {
            $id = $this->getRequest()->getParam('id');
            if ($this->adminHelper()->isGlobal()) {
                /* @var $customSettings Mana_Content_Model_Page_GlobalCustomSettings */
                $finalSettings = Mage::getModel('mana_sorting/method');

                if (!is_null($id)) {
                    $finalSettings->load($id);
                    if (!$finalSettings->getId()) {
                        throw new Mage_Core_Exception($this->__('This sorting method no longer exists.'));
                    }
                } else {
                    $finalSettings->setDefaults();
                }
                $customSettings = $finalSettings;
            } else {
                if (!is_null($id)) {
                    /* @var $customSettings Mana_Sorting_Model_Method_StoreCustomSettings */
                    $customSettings = Mage::getModel('mana_sorting/method_storeCustomSettings');

                    /* @var $finalSettings Mana_Sorting_Model_Method_StoreCustomSettings */
                    $finalSettings = Mage::getModel('mana_sorting/method_store');

                    $finalSettings->setData('store_id', $this->adminHelper()->getStore()->getId());
                    $finalSettings->load($id, 'method_id');

                    if (!$finalSettings->getId()) {
                        throw new Mage_Core_Exception($this->__('This sorting method no longer exists.'));
                    }


                    /* @var $finalGlobalSettings Mana_Sorting_Model_Method */
                    $finalGlobalSettings = Mage::getModel('mana_sorting/method');
                    $finalGlobalSettings->load($id);
                    $customGlobalSettings = $finalGlobalSettings;

                    Mage::register('m_global_edit_model', $customGlobalSettings);
                    Mage::register('m_global_flat_model', $finalGlobalSettings);

                    if ($customSettingsId = $finalSettings->getData('method_store_custom_settings_id')) {
                        $customSettings->load($customSettingsId);
                    } else {
                        $customSettings
                            ->setData('store_id', $this->adminHelper()->getStore()->getId())
                            ->setData('page_global_id', $finalGlobalSettings->getId());
                    }
                } else {
                    throw new Mage_Core_Exception($this->__('Non existent sorting methods can not be customized on store level.'));
                }
            }
            Mage::register('m_edit_model', $customSettings);
            Mage::register('m_flat_model', $finalSettings);
        } else {
            $finalSettings = Mage::registry('m_flat_model');
        }

        return compact('customSettings', 'finalSettings');
    }

    public function indexAction() {
        // page
        $this->_title('Mana')->_title($this->__('Sorting Method Management'));
        $this->loadLayout();
        $this->renderLayout();
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function editAction() {
        try {
            $models = $this->_registerModels();
            Mage::dispatchEvent('m_load_related_products');
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('*/*/');
            return;
        }

        /* @var $model Mana_Sorting_Model_Method_Abstract */
        $model = $models['finalSettings'];

        // page
        if ($model->getId()) {
            $this->_title('Mana')->_title($this->__('%s - Sorting Method', $model->getData('title')));
        }
        else {
            $this->_title('Mana')->_title($this->__('New Sorting Method'));
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
        $this->_setActiveMenu('mana/sortingmethod');
        $this->renderLayout();
    }

    public function saveAction() {
        // data
        $models = $this->_registerModels();
        $response = new Varien_Object();

        /* @var $messages Mage_Adminhtml_Block_Messages */
        $messages = $this->getLayout()->createBlock('adminhtml/messages');

        /* @var $model Mana_Sorting_Model_Method_Abstract */
        $model = $models['customSettings'];

        $refreshNewPage = $this->adminHelper()->isGlobal() && !$model->getId();
        try {
            $this->_processChanges();

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
            $messages->addSuccess($this->__('Your changes are successfully saved.'));
        } catch (Mana_Core_Exception_Validation $e) {
            foreach ($e->getErrors() as $error) {
                $messages->addError($error);
            }
            $response->setData('failed', true);
        } catch (Exception $e) {
            $messages->addError($e->getMessage());
            $response->setData('failed', true);
        }

        $update['#messages'] = $messages->getGroupedHtml();
        $response->setData('updates', $update);
        if ($refreshNewPage) {
            $response->setData(
                'forceEditUrl',
                $this->adminHelper()->getStoreUrl(
                    '*/*/edit',
                    array(
                        'id' => $model->getId()
                    )
                )
            );
        }
        $this->getResponse()->setBody($response->toJson());
    }

    protected function _processChanges() {
        // data
        $models = $this->_registerModels();

        /* @var $model Mana_Sorting_Model_Method_Abstract */
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
                $model->unsetData($key);
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
//        foreach (array('option_page_available_sort_by') as $key) {
//            if ($model->hasData($key) && is_array($model->getData($key))) {
//                $model->setData($key, implode(',', $model->getData($key)));
//            }
//        }

        // validate if all required data is entered and makes sense
        $model->validate();
    }


    public function deleteAction() {
        if (!$this->adminHelper()->isGlobal()) {
            $this->getSessionSingleton()->addError("Sorting methods can only be deleted globally");
            $this->_redirect('*/*/');

            return;
        }

        try {
            $models = $this->_registerModels();
            /* @var $model Mana_Sorting_Model_Method_Abstract */
            $model = $models['finalSettings'];
            $model->delete();

            $model = $models['customSettings'];
            $model->delete();
            $this->getSessionSingleton()->addSuccess($this->__('Sorting method has been deleted successfully!'));
        } catch (Exception $e) {
            $this->getSessionSingleton()->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }

    #region Dependencies

    /**
     * @return Mana_Sorting_Helper_Data
     */
    public function sortingHelper() {
        return Mage::helper('mana_sorting');
    }


    #endregion
} 