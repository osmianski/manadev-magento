<?php
/** 
 * @category    Mana
 * @package     Mana_Page
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Page_Resource_Special  {
    protected $_data = array();
    protected $_key = 'mana_page/special/definitions';

    public function getData($storeId) {
        if (!isset($this->_data[$storeId])) {
            $data = Mage::getStoreConfig($this->_key);
            $data = $data ? json_decode($data, true) : array();

            $globalData = isset($data[0]) ? $data[0] : array();
            if ($storeId) {
                $storeData = isset($data[$storeId]) ? $data[$storeId] : array();
                foreach ($globalData as $id => $globalModel) {
                    if (isset($globalModel['default_mask0'])) {
                        unset($globalModel['default_mask0']);
                    }
                    $storeData[$id] = isset($storeData[$id]) ? array_merge($globalModel, $storeData[$id]) : $globalModel;
                }
            }
            else {
                $storeData = $globalData;
            }

            $this->_data[$storeId] = $storeData;
        }

        return $this->_data[$storeId];
    }

    /**
     * @param $id
     * @param $storeId
     * @return bool|Mana_Page_Model_Special
     */
    public function getModel($id, $storeId) {
        $data = $this->getData($storeId);
        if (isset($data[$id])) {
            /* @var $model Mana_Page_Model_Special */
            $model = Mage::getModel('mana_page/special');
            $model->setData($data[$id]);
            $model->setData('id', $id);
            if ($storeId) {
                $model->setData('store_id', $storeId);
            }
            return $model;
        }
        else {
            return false;
        }
    }

    /**
     * @param Mana_Page_Model_Special $model
     */
    public function saveModel($model) {
        /* @var $t Mana_Page_Helper_Data */
        $t = Mage::helper('mana_page');

        $data = Mage::getStoreConfig($this->_key);
        $data = $data ? json_decode($data, true) : array();

        $storeId = 0;
        if ($model->hasData('store_id')) {
            $storeId = $model->getData('store_id');
            $model->unsetData('store_id');
        }

        $id = 0;
        if ($model->hasData('id')) {
            $id = $model->getData('id');
            $model->unsetData('id');
        }

        if (!isset($data[$storeId])) {
            $data[$storeId] = array();
        }

        if ($model->hasData('default_mask0')) {
            if (!$this->dbHelper()->isModelContainsCustomSetting($model, Mana_Page_Model_Special::DM_TITLE)) {
                $model->unsetData('title');
            }
            if (!$this->dbHelper()->isModelContainsCustomSetting($model, Mana_Page_Model_Special::DM_URL_KEY)) {
                $model->unsetData('url_key');
            }
            if (!$this->dbHelper()->isModelContainsCustomSetting($model, Mana_Page_Model_Special::DM_POSITION)) {
                $model->unsetData('position');
            }
        }

        $modelData = $model->getData();
        if ($id) {
            if ($storeId) {
                if (count($modelData)) {
                    $data[$storeId][$id] = $modelData;
                }
                else {
                    if (isset($data[$storeId][$id])) {
                        unset($data[$storeId][$id]);
                    }
                }
            }
            else {
                $data[$storeId][$id] = $modelData;
            }
        }
        else {
            if ($storeId) {
                throw new Mage_Core_Exception($t->__('Non existent special condition can not be customized on store level.'));
            }
            else {
                $id = count($data[$storeId]) ? max(array_keys($data[$storeId])) + 1 : 1;
                $model->setData('id', $id);
                $data[$storeId][$id] = $modelData;
            }
        }

        $this->_saveConfig($data);
    }

    #region Dependencies

    /**
     * @return Mana_Core_Helper_Db
     */
    public function dbHelper() {
        return Mage::helper('mana_core/db');
    }

    public function delete($id) {
        $data = Mage::getStoreConfig($this->_key);
        $data = $data ? json_decode($data, true) : array();
        foreach (array_keys($data) as $storeId) {
            if (isset($data[$storeId][$id])) {
                unset($data[$storeId][$id]);
            }
        }

        $this->_saveConfig($data);
    }

    protected function _saveConfig($data) {
        $scope = 'default';
        $scopeId = 0;

        /* @var $collection Mage_Core_Model_Mysql4_Config_Data_Collection */
        $collection = Mage::getModel('core/config_data')->getCollection();

        $collection->getSelect()
            ->where('scope=?', $scope)
            ->where('scope_id=?', $scopeId)
            ->where('path=?', $this->_key);

        /** @noinspection PhpUnusedLocalVariableInspection */
        $configModel = null;
        foreach ($collection as $configModel) {
            break;
        }

        if (!$configModel) {
            $configModel = Mage::getModel('core/config_data');
            $configModel->setData('scope', $scope);
            $configModel->setData('scope_id', $scopeId);
            $configModel->setData('path', $this->_key);
        }

        $configModel->setData('value', json_encode($data));
        $configModel->save();

        $this->_data = array();
        Mage::getConfig()->reinit();
        Mage::app()->reinitStores();
    }

    #endregion
}