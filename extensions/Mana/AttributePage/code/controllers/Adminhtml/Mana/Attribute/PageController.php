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
    protected function _save(&$response) {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper('mana_core');
        /* @var $ajax Mana_Ajax_Helper_Data */
        $ajax = Mage::helper('mana_ajax');

        /* @var $db Mana_Db_Helper_Data */
        $db = Mage::helper('mana_db');

        if (!($sessionId = $this->getRequest()->getParam('sessionId'))) {
            throw new Mage_Core_Exception($db->__('Page editing session is required but not provided.'));
        }
        if ($db->isEditingSessionExpired($this->getRequest()->getParam('sessionId'))) {
            throw new Mage_Core_Exception($db->__('Page editing session is expired. Please reload the page.'));
        }

        /* @var $pageDataSource Mana_Admin_Block_Data_Entity */
        $pageDataSource = $this->getLayout()->getBlock('page.data_source');

        $model = $pageDataSource->loadModel();

        $model->getResource()->beginTransaction();

        try {
            if (!$model->getId()) {
                $model->assignDefaultValues();
            }
            // add field data
            $model->disableIndexing()->validate()->save();

            foreach ($pageDataSource->loadAdditionalModels() as $key => $additionalModel) {
                if (!$model->getId()) {
                    $additionalModel->assignDefaultValues();
                }
                // add field data
                $additionalModel->disableIndexing()->validate()->save();
            }

            foreach ($pageDataSource->getChildDataSources() as $childDataSource) {
                /* @var $grid Mana_Admin_Block_Grid */
                $grid = $childDataSource->getParentBlock();
                /* @var $childDataSource Mana_Admin_Block_Data_Collection */
                $edit = $this->getRequest()->getParam($core->getBlockAlias($grid));

                $childDataSource
                    ->processPendingEdits($sessionId, $edit)
                    ->saveEditedData($sessionId, $edit, true);

                $grid->setEdit($edit);
                if (!isset($response['blocks'])) {
                    $response['blocks'] = array();
                }
                $response['blocks'][$grid->getNameInLayout()] = $ajax->renderBlock($grid->getNameInLayout());
            }

            $model->postValidate();
            foreach ($pageDataSource->loadAdditionalModels() as $additionalModel) {
                $additionalModel->postValidate();
            }
            foreach ($pageDataSource->getChildDataSources() as $childDataSource) {
                /* @var $childModel Mana_Db_Model_Entity */
                foreach ($childDataSource->createCollection()->setEditFilter(true) as $childModel) {
                    $childModel->postValidate();
                }
            }

            $model->updateIndexes();
            foreach ($pageDataSource->loadAdditionalModels() as $additionalModel) {
                $additionalModel->updateIndexes();
            }
            foreach ($pageDataSource->getChildDataSources() as $childDataSource) {
                /* @var $childModel Mana_Db_Model_Entity */
                foreach ($childDataSource->createCollection()->setEditFilter(true) as $childModel) {
                    $childModel->updateIndexes();
                }
            }
            $model->getResource()->commit();
        }
        catch (Exception $e) {
            $model->getResource()->rollBack();
            throw $e;
        }

    }
}