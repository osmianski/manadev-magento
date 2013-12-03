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
                if ($id = $this->getRequest()->getParam('id')) {
                    $finalSettings->load($id);
                    $customSettings->load($finalSettings->getData('attribute_page_global_custom_settings_id'));
                }
            }
            else {
                $customSettings = Mage::getModel('mana_attributepage/attributePage_storeCustomSettings');
                $finalSettings = Mage::getModel('mana_attributepage/attributePage_store');
                $storeId = $this->adminHelper()->getStore()->getId();
                if ($id = $this->getRequest()->getParam('id')) {
                    $customSettings->loadForStore($id, $storeId);
                    $finalSettings->loadForStore($id, $storeId, 'primary_global_id');
                }
                if (!$customSettings->getId()) {
                    $customSettings
                        ->setStoreId($this->adminHelper()->getStore()->getId())
                        ->setData('global_id', $id);
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
        $models = $this->_registerModels();
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
        $this->_setActiveMenu('mana/attributepage');
        $this->renderLayout();
     }
}