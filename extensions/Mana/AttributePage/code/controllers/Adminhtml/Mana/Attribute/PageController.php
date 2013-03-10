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
class Mana_AttributePage_Adminhtml_Mana_Attribute_PageController extends Mana_Admin_Controller {
    protected function _save() {
        /* @var $db Mana_Db_Helper_Data */
        $db = Mage::helper('mana_db');

        if ($db->isEditingSessionExpired($this->getRequest()->getParam('sessionId'))) {
            throw new Mage_Core_Exception($db->__('Page editing session is expired. Please reload the page.'));
        }

        /* @var $pageDataSource Mana_Admin_Block_Data_Entity */
        $pageDataSource = $this->getLayout()->getBlock('page.data_source');

        $model = $pageDataSource->loadModel();
        if (!$model->getId()) {
            $model->assignDefaultValues();
        }
        // add field data
        $model->disableIndexing()->save();

        foreach ($pageDataSource->loadAdditionalModels() as $key => $additionalModel) {
            if (!$model->getId()) {
                $additionalModel->assignDefaultValues();
            }
            // add field data
            $additionalModel->disableIndexing()->save();
        }

        foreach ($pageDataSource->getChildDataSources() as $childDataSource) {
            /* @var $childDataSource Mana_Admin_Block_Data_Collection */
            $childDataSource
                ->processPendingEdits()
                ->saveEditedData();

        }

        $model->updateIndexes();
        foreach ($pageDataSource->loadAdditionalModels() as $key => $additionalModel) {
            $additionalModel->updateIndexes();
        }


    }
}