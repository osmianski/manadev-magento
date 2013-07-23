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
class Mana_Seo_Adminhtml_Mana_Seo_SchemaController extends Mana_Admin_Controller_V2_Controller {
    /**
     * There are 4 related tables (referred to as "scopes") storing data of the same entity: global, store, flat and store_flat.
     * Their relations:
     * global:
     *      id - globally unique entity id
     * flat:
     *      id - id of flattened record
     *      primary_id - global.id
     * store:
     *      global_id - global.id
     *      store_id - ID of store this record is tailored to
     * store_flat
     *      primary_global_id - global.id
     *      global_id - flat.id
     *      primary_id - store.id
     *      store_id - ID of store this record is tailored to
     *
     * @return Mana_Seo_Model_Schema[]
     */
    protected function _registerModels() {
        if (!($edit = Mage::registry('m_edit_model'))) {
            if ($this->adminHelper()->isGlobal()) {
                $edit = $this->dbHelper()->getModel('mana_seo/schema/global');
                $flat = $this->dbHelper()->getModel('mana_seo/schema/flat');
                if ($id = $this->getRequest()->getParam('id')) {
                    $edit->load($id);
                    $flat->load($id, 'primary_id');
                }
            }
            else {
                $edit = $this->dbHelper()->getModel('mana_seo/schema/store');
                $flat = $this->dbHelper()->getModel('mana_seo/schema/store_flat');
                $storeId = $this->adminHelper()->getStore()->getId();
                if ($id = $this->getRequest()->getParam('id')) {
                    $edit->loadForStore($id, $storeId);
                    $flat->loadForStore($id, $storeId, 'primary_global_id');
                }
                if (!$edit->getId()) {
                    $edit
                        ->setStoreId($this->adminHelper()->getStore()->getId())
                        ->setData('global_id', $id);
                }
            }
            Mage::register('m_edit_model', $edit);
            Mage::register('m_flat_model', $flat);
        }
        else {
            $flat = Mage::registry('m_flat_model');
        }

        return compact('edit', 'flat');
    }

    public function indexAction() {
        // page
        $this->_title('MANAdev')->_title($this->__('SEO Schemas'));

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
        $this->_setActiveMenu('mana/seo_schema');
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
        $models = $this->_registerModels();

        // page
        $this->_title('Mana')->_title($this->__('%s - SEO Schema', $models['flat']->getName()));

        // layout
        $update = $this->getLayout()->getUpdate();
        $update->addHandle('default');
        $this->addActionLayoutHandles();

        if (!Mage::app()->isSingleStoreMode()) {
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
        $this->_setActiveMenu('mana/seo_schema');
        $this->renderLayout();
    }

    public function symbolGridAction() {
        $this->_registerModels();

        // process in-grid edits
        if ($raw = $this->getRequest()->getParam('raw', null)) {
            $raw = json_decode($raw, true);
            if ($edit = $this->getRequest()->getParam('edit', null)) {
                $edit = json_decode($edit, true);
                $this->_processPendingRawEdits($raw, $edit);
                if ($action = $this->getRequest()->getParam('action')) {
                    switch ($action) {
                        case 'add':
                            $id = max(array_keys($raw)) + 1;
                            $raw[$id] = array('symbol' => '', 'substitute' => '');
                            $edit['saved'][$id] = $id;
                            break;
                        case 'remove':
                            foreach (array_keys($raw) as $id) {
                                if (!empty($raw[$id]['edit_massaction'])) {
                                    unset($raw[$id]);
                                    $edit['deleted'][$id] = $id;
                                }
                            }
                            break;

                    }
                }
            }
        }
        else {
            $edit = null;
        }

        // layout
        $this->addActionLayoutHandles();
        $this->loadLayoutUpdates();
        $this->generateLayoutXml()->generateLayoutBlocks();
        $this->_isLayoutLoaded = true;

        // set grid properties
        /* @var $grid Mana_Seo_Block_Adminhtml_Schema_SymbolGrid */
        $grid = $this->getLayout()->getBlock('symbol_grid');
        if ($edit) {
            $grid->setEdit($edit);
        }
        if ($raw) {
            $grid->setRaw($raw);
        }

        // render AJAX result
        $this->renderLayout();
    }

    public function toolbarGridAction() {
        $this->_registerModels();

        // process in-grid edits and grid actions
        if ($raw = $this->getRequest()->getParam('raw', null)) {
            $raw = json_decode($raw, true);
            if ($edit = $this->getRequest()->getParam('edit', null)) {
                $edit = json_decode($edit, true);
                $this->_processPendingRawEdits($raw, $edit);
            }
        }
        else {
            $edit = null;
        }

        // layout
        $this->addActionLayoutHandles();
        $this->loadLayoutUpdates();
        $this->generateLayoutXml()->generateLayoutBlocks();
        $this->_isLayoutLoaded = true;

        // set grid properties
        /* @var $grid Mana_Seo_Block_Adminhtml_Schema_ToolbarGrid */
        $grid = $this->getLayout()->getBlock('toolbar_grid');
        if ($edit) {
            $grid->setEdit($edit);
        }
        if ($raw) {
            $grid->setRaw($raw);
        }

        // render AJAX result
        $this->renderLayout();
    }

    protected function _processChanges() {
        // data
        $models = $this->_registerModels();

        // processing
        if ($fields = $this->getRequest()->getPost('fields')) {
            foreach ($fields as $key => $value) {
                $models['edit']->overrideData($key, $value);
            }
        }
        if ($useDefault = $this->getRequest()->getPost('use_default')) {
            foreach ($useDefault as $key) {
                $models['edit']->useDefaultData($key);
            }
        }
        foreach (array('symbol-grid' => 'symbols', 'toolbar-grid' => 'toolbar_url_keys') as $field => $key) {
            if ($json = $this->getRequest()->getPost($field)) {
                $json = json_decode($json, true);
                $raw = json_decode($json['raw'], true);
                if ($edit = json_decode($json['edit'], true)) {
                    $this->_processPendingRawEdits($raw, $edit);
                }
                if (empty($edit['useDefault'])) {
                    $models['edit']->overrideData($key, json_encode($raw));
                }
                else {
                    $models['edit']->useDefaultData($key);
                }
            }
        }
    }
    public function beforeSaveAction() {
        // data
        $models = $this->_registerModels();
        $response = new Varien_Object();
        $messages = array();

        try {
            $this->_processChanges();

            $affectsUrl = false;
            if ($models['flat']->getStatus() == Mana_Seo_Model_Schema::STATUS_ACTIVE) {
                foreach ($models['flat']->getFieldsAffectingUrl() as $key) {
                    if ($models['flat']->isDefaultable($key) &&
                        $models['edit']->isUsingDefaultData($key) != $models['edit']->isUsingDefaultData($key, '_origData'))
                    {
                        $affectsUrl = true;
                        break;
                    }
                    elseif (!$models['edit']->isUsingDefaultData($key) &&
                        $models['flat']->getData($key) !== $models['edit']->getData($key))
                    {
                        $affectsUrl = true;
                        break;
                    }
                }
            }

            if ($affectsUrl) {
                $response->setData('affectsUrl', $this->seoHelper()->__('One or more edited fields affects the way URL is constructed. It is recommended to save previous schema settings as separate obsolete schema when old structure URL may have been indexed by search bots, so that URLs with old structure would be redirected to this schema URLs. Do you want to save previous schema settings as separate obsolete schema?'));
            }

        } catch (Mana_Db_Exception_Validation $e) {
            foreach ($e->getErrors() as $error) {
                $messages[] = $error;
            }
        }
        catch (Exception $e) {
            $messages[] = $e->getMessage();
        }
        if (count($messages)) {
            $response->setData('messages', $messages);
        }
        $this->getResponse()->setBody($response->toJson());
    }
    public function saveAction() {
        // data
        $models = $this->_registerModels();
        $response = new Varien_Object();

        /* @var $messages Mage_Adminhtml_Block_Messages */
        $messages = $this->getLayout()->createBlock('adminhtml/messages');

        try {
            $this->_processChanges();

            if ($this->adminHelper()->isGlobal() && $models['edit']->getStatus() == Mana_Seo_Model_Schema::STATUS_ACTIVE) {
                $activeSchema = $this->seoHelper()->getActiveSchema(Mage_Core_Model_App::ADMIN_STORE_ID, false);
                if ($activeSchema && $activeSchema->getId() && $activeSchema->getId() != $models['edit']->getId()) {
                    $activeSchema->overrideStatus(Mana_Seo_Model_Schema::STATUS_OBSOLETE)->save();
                }
            }

            if ($this->getRequest()->getPost('createObsoleteCopy')) {
                if ($id = $this->getRequest()->getParam('id')) {
                    $this->_duplicate($id);
                }
            }

            // do save
            $models['edit']->save();
            Mage::dispatchEvent('m_saved', array('object' => $models['edit']));
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

    public function deleteAction() {
        $models = $this->_registerModels();
        if ($models['edit']->getId()) {
            if ($models['edit']->getStatus() != Mana_Seo_Model_Schema::STATUS_ACTIVE) {
                $models['edit']->delete();
                $this->getSessionSingleton()->addSuccess($this->seoHelper()->__('SEO schema is deleted successfully!'));
                $this->_redirect('*/*/');
            }
            else {
                $this->getSessionSingleton()->addError($this->seoHelper()->__("You can't delete active SEO schema."));
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        else {
            $this->getSessionSingleton()->addError($this->seoHelper()->__('SEO schema is already deleted.'));
            $this->_redirect('*/*/');
        }
    }

    protected function _duplicate($id) {
        /* @var $original Mana_Seo_Model_Schema */
        $original = $this->dbHelper()->getModel('mana_seo/schema/global');
        $original->load($id);

        /* @var $duplicate Mana_Seo_Model_Schema */
        $duplicate = $this->dbHelper()->getModel('mana_seo/schema/global');
        $duplicate
            ->setData($original->getData())
            ->unsetData('id');

        if ($duplicate->getStatus() == Mana_Seo_Model_Schema::STATUS_ACTIVE) {
            $duplicate->overrideStatus(Mana_Seo_Model_Schema::STATUS_OBSOLETE);
        }
        $duplicate->save();
        $result = $duplicate->getId();
        foreach (Mage::app()->getStores() as $store) {
            /* @var $store Mage_Core_Model_Store */
            $original = $this->dbHelper()->getModel('mana_seo/schema/store');
            $original->loadForStore($id, $store->getId());
            if ($original->getId()) {
                /* @var $duplicate Mana_Seo_Model_Schema */
                $duplicate = $this->dbHelper()->getModel('mana_seo/schema/store');
                $duplicate
                    ->setData($original->getData())
                    ->unsetData('id');

                if ($duplicate->getStatus() == Mana_Seo_Model_Schema::STATUS_ACTIVE) {
                    $duplicate->overrideStatus(Mana_Seo_Model_Schema::STATUS_OBSOLETE);
                }
                $duplicate->save();
            }
        }

        return $result;
    }

    public function duplicateAction() {
        $models = $this->_registerModels();
        if ($id = $models['edit']->getId()) {
            $id = $this->_duplicate($id);
            $this->getSessionSingleton()->addSuccess($this->seoHelper()->__('SEO schema is duplicated successfully! You can edit it here.'));
            $this->_redirect('*/*/edit', compact('id'));
        }
        else {
            $this->getSessionSingleton()->addError($this->seoHelper()->__('SEO schema not found.'));
            $this->_redirect('*/*/');
        }
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