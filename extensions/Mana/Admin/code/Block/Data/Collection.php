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
class Mana_Admin_Block_Data_Collection extends Mana_Admin_Block_Data {
    public function processPendingEdits(&$edit, $sessionId = null, $checkIfExpired = false) {
        /* @var $db Mana_Db_Helper_Data */
        $db = Mage::helper('mana_db');

        if (!$edit) {
            $edit = mage::app()->getRequest()->getParam('edit');
        }
        if ($edit) {
            if (!is_array($edit)) {
                $edit = json_decode($edit, true);
            }
            if (!$sessionId) {
                $sessionId = $edit['sessionId'];
            }

            if (($checkIfExpired || count($edit['pending'])) && $db->isEditingSessionExpired($sessionId)) {
                throw new Mage_Core_Exception($db->__('Page editing session is expired. Please reload the page.'));
            }

            foreach ($edit['pending'] as $id => $cells) {
                if (isset($edit['deleted'][$id])) {
                    continue;
                }

                if (!($model = $this->loadEditedModel($id, $sessionId))) {
                    $model = $this->loadModel($id);
                }
                else {
                    $edit['saved'][$model->getEditStatus()] = $model->getId();
                }

                $isOriginal = false;
                if (!$model->getEditStatus()) {
                    $isOriginal = true;
                    $data = $model->getData();
                    $model = $this->createModel();
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

        return $this;
    }

    /**
     * @param $sessionId
     * @param $edit
     * @param bool $disableIndexing
     * @param callable|null $beforeSaveCallback
     * @return \Mana_Admin_Block_Data_Collection
     */
    public function saveEditedData($edit, $sessionId, $disableIndexing = false, $beforeSaveCallback = null) {
        /* @var $dbConfig Mana_Db_Helper_Config */
        $dbConfig = Mage::helper('mana_db/config');

        $foreignKey = $dbConfig->getForeignKey($this->getParentDataSource()->getEntity(), $this->getEntity());

        foreach ($edit['saved'] as $id => $editId) {
            if ($id != $editId) {
                $editModel = $this->loadModel($editId);
                $data = $editModel->getData();
                unset($data['id']);
                $data['edit_status'] = 0;
                $data['edit_session_id'] = 0;
                $data['edit_massaction'] = 0;
                $model = $this->loadModel($id);
                $model->addData($data);
                if ($beforeSaveCallback) {
                    call_user_func($beforeSaveCallback, $model, $editModel);
                }
                if ($disableIndexing) {
                    $model->disableIndexing();
                }
                $model->validate($this)->save();
                $editModel->delete();
            }
            else {
                $model = $this->loadModel($id);
                $model->setData($foreignKey, $this->getParentModel()->getId());
                $model->setEditStatus(0);
                $model->setEditSessionId(0);
                $model->setEditMassaction(0);
                if ($beforeSaveCallback) {
                    call_user_func($beforeSaveCallback, $model, null);
                }
                if ($disableIndexing) {
                    $model->disableIndexing();
                }
                $model->validate($this)->save();
            }
        }
        foreach ($edit['deleted'] as $id) {
            $model = $this->loadModel($id);
            $model->delete();
        }

        return $this;
    }

    public function createCollection() {
        /* @var $db Mana_Db_Helper_Data */
        $db = Mage::helper('mana_db');

        return $db->getResourceModel($this->getEntity() . '_collection');
    }

    /**
     * @return Mage_Core_Model_Abstract
     */
    public function getParentModel() {
        return $this->getParentDataSource()->loadModel();
    }

    public function getParentCondition() {
        /* @var $dbConfig Mana_Db_Helper_Config */
        $dbConfig = Mage::helper('mana_db/config');

        $foreignKey = $dbConfig->getForeignKey($this->getParentDataSource()->getEntity(), $this->getEntity());

        $model = $this->getParentModel();
        if ($model->getId()) {
            return $foreignKey . ' = ' . $model->getId();
        }
        else {
            return $foreignKey . ' IS NULL';
        }
    }

    /**
     * @return Mana_Db_Model_Entity
     */
    public function createModel() {
        /* @var $db Mana_Db_Helper_Data */
        $db = Mage::helper('mana_db');

        return $db->getModel($this->getEntity());
    }

    /**
     * @param $id
     * @return Mana_Db_Model_Entity
     */
    public function loadModel($id) {
        /* @var $db Mana_Db_Helper_Data */
        $db = Mage::helper('mana_db');

        return $db->getModel($this->getEntity())->load($id);
    }

    /**
     * @param $edit
     * @return Mana_Db_Resource_Entity_Collection
     */
    public function loadModels($edit) {
        /* @var $db Mana_Db_Helper_Data */
        $db = Mage::helper('mana_db');
        /* @var $collection Mana_Db_Resource_Entity_Collection */
        $collection = $this->createCollection();
        $collection
            ->setEditFilter($edit)
            ->addFieldToFilter('edit_massaction', 1);

        return $collection;
    }

    /**
     * @param $id
     * @param $sessionId
     * @return Mana_Db_Model_Entity
     */
    public function loadEditedModel($id, $sessionId) {
        /* @var $db Mana_Db_Helper_Data */
        $db = Mage::helper('mana_db');

        $result = $db->getModel($this->getEntity())->loadEdited($id, $sessionId);

        return $result->getId() ? $result : null;
    }

    public function getLabel($entity, $field) {
        /* @var $grid Mana_Admin_Block_Grid */
        $grid = $this->getParentBlock();
        if ($column = $grid->getChild($grid->getNameinLayout().'.'.$field)) {
            return $column->getTitle();
        }
        else {
            return $field;
        }
    }
}