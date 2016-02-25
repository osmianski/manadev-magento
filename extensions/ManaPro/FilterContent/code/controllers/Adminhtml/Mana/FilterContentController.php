<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterContent
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_FilterContent_Adminhtml_Mana_FilterContentController extends Mana_Admin_Controller_V2_Controller {
    protected function _registerModels() {
        if (!($customSettings = Mage::registry('m_edit_model'))) {
            if ($this->adminHelper()->isGlobal()) {
                $customSettings = Mage::getModel('manapro_filtercontent/globalCustomSettings');
                $finalSettings = Mage::getModel('manapro_filtercontent/global');
            }
            else {
                throw new Exception('Not implemented');
            }
            Mage::register('m_edit_model', $customSettings);
            Mage::register('m_flat_model', $finalSettings);
        }
        else {
            $finalSettings = Mage::registry('m_flat_model');
        }

        return compact('customSettings', 'finalSettings');
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function editAction() {
        try {
            $models = $this->_registerModels();
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('*/*/');
            return;
        }

        /* @var $model ManaPro_FilterContent_Model_Abstract */
        $model = $models['finalSettings'];

        // page
        if ($model->getId()) {
            $this->_title('Mana')->_title($this->__('Filter Specific Content Applied When %s', $model->getData('title')));
        }
        else {
            $this->_title('Mana')->_title($this->__('New Filter Specific Content'));
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

        // simplify if one tab
        if (($tabs = $this->getLayout()->getBlock('tabs')) && count($tabs->getChild()) == 1) {
            /* @var $tabs Mana_Admin_Block_V2_Tabs */
            $content = $tabs->getActiveTabBlock();
            $tabs->getParentBlock()->unsetChild('tabs');
            $this->getLayout()->getBlock('container')->insert($content, $content->getNameInLayout(), null, $content->getNameInLayout());
            $content->addToParentGroup('content');
        }

        // rendering
        $this->_setActiveMenu('mana/filtercontent');
        $this->renderLayout();
    }

    public function newConditionAction()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $data = array(
            'type' => $type,
            'rule' => Mage::getModel('manapro_filtercontent/global'),
            'prefix' => 'conditions',
        );
        if (!empty($typeArr[1])) {
            $data['attribute'] = $typeArr[1];
        }
        $model = Mage::getModel($type, $data)->setId($id);

        if ($model instanceof Mage_Rule_Model_Condition_Abstract) {
            $model->setData('js_form_object', $this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }

    protected function _isAllowed() {
        return true;
    }

}