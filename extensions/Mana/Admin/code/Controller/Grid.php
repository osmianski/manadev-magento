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

    protected function _renderGrid() {
        /* @var $adminPageHelper Mana_Admin_Helper_Page */
        $adminPageHelper = Mage::helper('mana_admin/page');
        $this->_gridLayout = $adminPageHelper->getGridLayout($this->getRequest());

        /* @var $ajax Mana_Ajax_Helper_Data */
        $ajax = Mage::helper('mana_ajax');

        $ajax->processPageWithoutRendering($this->_gridLayout['route'], array($this, '_endRenderingGrid'));
    }

    public function _endRenderingGrid() {
        /* @var $ajax Mana_Ajax_Helper_Data */
        $ajax = Mage::helper('mana_ajax');
        $this->getResponse()->setBody($ajax->renderBlock($this->_gridLayout['block']));
    }

    protected function _addRow() {
        $model = $this->loadModel($type);
        $model->setEditStatus(-1)->setEditSessionId($edit['sessionId']);
        if (!$this->isGlobal()) {
            $model->setStoreId($this->getStore()->getId());
        }
        $model->assignDefaultValues();
        $model->save();
        $edit['saved'][$model->getId()] = $model->getId();
    }

    protected function _processPendingEdits() {
        $product = $this->_initProduct();
        Mage::register('m_crud_model', $product);
        $edit = null;
        if ($edit = $this->getRequest()->getParam('m-edit')) {
            $edit = json_decode(base64_decode($edit), true);
            Mage::helper('mana_admin')->processPendingEdits('manapro_video/video', $edit);
        }
    }

    public function indexAction() {
        $this->_renderGrid();
    }

    public function addAction() {
        $this->_renderGrid();
    }

    public function removeAction() {
        $this->_renderGrid();
    }
}