<?php
/**
 * @category    Mana
 * @package     Mana_Admin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Admin_Controller_Grid extends Mage_Adminhtml_Controller_Action {
    protected $_gridLayout;
    protected $_edit;

    protected function _loadLayout($callback) {
        /* @var $adminPageHelper Mana_Admin_Helper_Page */
        $adminPageHelper = Mage::helper('mana_admin/page');
        $this->_gridLayout = $adminPageHelper->getGridLayout($this->getRequest());

        /* @var $ajax Mana_Ajax_Helper_Data */
        $ajax = Mage::helper('mana_ajax');

        $ajax->processPageWithoutRendering($this->_gridLayout['route'], $callback);
    }

    protected function _render() {
        $this->_getBlock()->setEdit($this->_edit);

        /* @var $ajax Mana_Ajax_Helper_Data */
        $ajax = Mage::helper('mana_ajax');
        $this->getResponse()->setBody($ajax->renderBlock($this->_gridLayout['block']));
    }

    /**
     * @return Mana_Admin_Block_Grid
     */
    protected function _getBlock() {
        return $this->getLayout()->getBlock($this->_gridLayout['block']);
    }

    protected function _getParentModel() {
        return $this->_getBlock()->getParentModel();
    }

    protected function _createModel() {
        return $this->_getBlock()->createModel();
    }

    protected function _loadModel($id) {
        return $this->_getBlock()->loadModel($id);
    }

    protected function _loadModels($edit) {
        return $this->_getBlock()->loadModels($edit);
    }

    protected function _loadEditedModel($id, $sessionId) {
        return $this->_getBlock()->loadEditedModel($id, $sessionId);
    }

    protected function _add() {
        if ($edit = &$this->_edit) {
            $model = $this->_createModel();
            $model->setEditStatus(-1)->setEditSessionId($edit['sessionId']);
            $model->assignDefaultValues();
            $model->save();
            $edit['saved'][$model->getId()] = $model->getId();
        }

        return $this;
    }

    protected function _remove() {
        if ($edit = &$this->_edit) {
            $models = $this->_loadModels($edit);
            if (count($models)) {
                foreach ($models as $model) {
                    /* @var $model Mana_Db_Model_Entity */
                    // TD: review this
//                    if (!$this->isGlobal() && $model->getGlobalId()) {
//                        throw new Mage_Core_Exception($this->__('On store level, you can only delete rows which are specific to this store.'));
//                    }
                    if (($id = array_search($model->getId(), $edit['saved'])) !== false) {
                        if ($id != $model->getId()) {
                            // modified
                            $edit['deleted'][$id] = $id;
                            unset($edit['saved'][$id]);
                        }
                        else {
                            // new
                            unset($edit['saved'][$model->getId()]);
                        }
                        $model->delete();
                    }
                    else {
                        // mot modified
                        $edit['deleted'][$model->getId()] = $model->getId();
                    }
                }
            }
            else {
                throw new Mage_Core_Exception($this->__('Please select at least one grid row first.'));
            }
        }

        return $this;
    }

    protected function _processPendingEdits() {
        $parentModel = $this->_getParentModel();
        Mage::register('m_page_model', $parentModel);
        $edit = null;
        if ($edit = $this->getRequest()->getParam('edit')) {
            $edit = json_decode($edit, true);

            foreach ($edit['pending'] as $id => $cells) {
                if (isset($edit['deleted'][$id])) {
                    continue;
                }

                if (!($model = $this->_loadEditedModel($id, $edit['sessionId']))) {
                    $model = $this->_loadModel($id);
                }
                else {
                    $edit['saved'][$model->getEditStatus()] = $model->getId();
                }

                $isOriginal = false;
                if (!$model->getEditStatus()) {
                    $isOriginal = true;
                    $data = $model->getData();
                    $model = $this->_createModel();
                    $status = $data['id'];
                    unset($data['id']);
                    $model->addData($data);
                    $model->setEditStatus($status)->setEditSessionId($edit['sessionId']);
                }

                $model->addGridCellData($cells);
                $model->save();
                if ($isOriginal) {
                    $edit['saved'][$id] = $model->getId();
                }
            }
            $edit['pending'] = array();
        }
        $this->_edit = $edit;
        return $this;
    }

    public function indexAction() {
        $this->_loadLayout(array($this, '_indexResponse'));
    }
    public function _indexResponse() {
        try {
            $this->_processPendingEdits()->_render();
        }
        catch (Mage_Core_exception $e) {
            $this->getResponse()->setBody(json_encode(array('error' => true, 'message' => $e->getMessage())));
        }
    }
    public function addAction() {
        $this->_loadLayout(array($this, '_addResponse'));
    }

    public function _addResponse() {
        try {
            $this->_processPendingEdits()->_add()->_render();
        } catch (Mage_Core_exception $e) {
            $this->getResponse()->setBody(json_encode(array('error' => true, 'message' => $e->getMessage())));
        }
    }

    public function removeAction() {
        $this->_loadLayout(array($this, '_removeResponse'));
    }

    public function _removeResponse() {
        try {
            $this->_processPendingEdits()->_remove()->_render();
        } catch (Mage_Core_exception $e) {
            $this->getResponse()->setBody(json_encode(array('error' => true, 'message' => $e->getMessage())));
        }
    }
}