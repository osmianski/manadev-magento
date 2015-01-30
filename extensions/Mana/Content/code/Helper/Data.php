<?php
/** 
 * @category    Mana
 * @package     Mana_Content
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * Generic helper functions for Mana_Content module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class Mana_Content_Helper_Data extends Mage_Core_Helper_Abstract {

    /**
     * @param null $id
     * @param bool $saveToRegistry
     * @return array
     * @throws Mage_Core_Exception
     */
    public function registerModels($id = null, $saveToRegistry = true) {
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
                        $params = Mage::app()->getRequest()->getPost();
                        if (isset($params['id']) &&
                            isset($params['changes']['created'][$params['id']]) &&
                            $fieldData = $params['changes']['created'][$params['id']]) {
                            $this->setModelData($customGlobalSettings, $fieldData);
                            $this->setModelData($finalGlobalSettings, $fieldData);
                        }
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
                $params = Mage::app()->getRequest()->getPost();
                if (isset($params['id']) &&
                    isset($params['changes']['created'][$params['id']]) &&
                    $fieldData = $params['changes']['created'][$params['id']]) {
                    $this->setModelData($customSettings, $fieldData);
                    $this->setModelData($finalSettings, $fieldData);
                }
                Mage::register('m_edit_model', $customSettings);
                Mage::register('m_flat_model', $finalSettings);
            }
        }
        else {
            $finalSettings = Mage::registry('m_flat_model');
        }

        return compact('customSettings', 'finalSettings');
    }


    /**
     * @param $model Mana_Content_Model_Page_Abstract
     * @param $fields array
     */
    public function setModelData($model, $fields, $setMaskValue = false) {
        foreach($fields as $field => $fieldData) {
            if(substr($field, 12) == "default_mask"){
                $model->setData($field, $fieldData['value']);
                unset($fields[$field]);
            }
        }
        foreach ($fields as $field => $fieldData) {
            if(isset($fieldData['value'])) {
                $model->setData($field, $fieldData['value']);
                if($setMaskValue) {
                    $this->coreDbHelper()->isModelContainsCustomSetting($model, $field, !($fieldData['isDefault'] === "true"));
              }
            }
        }
    }

    #region Dependencies
    /**
     * @return Mana_Admin_Helper_Data
     */
    public function adminHelper() {
        return Mage::helper('mana_admin');
    }

    /**
     * @return Mana_Core_Helper_Db
     */
    public function coreDbHelper() {
        return Mage::helper('mana_core/db');
    }
    #endregion

}