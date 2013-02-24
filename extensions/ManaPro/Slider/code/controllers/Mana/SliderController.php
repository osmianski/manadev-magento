<?php
/**
 * @category    Mana
 * @package     ManaPro_Slider
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
include BP.'/app/code/core/Mage/Widget/controllers/Adminhtml/Widget/InstanceController.php';

/**
 * @author Mana Team
 *
 */
class ManaPro_Slider_Mana_SliderController extends Mage_Widget_Adminhtml_Widget_InstanceController {
    protected function _getEntityName() {
        return 'mana_filters/slider';
    }
    public function productGridAction() {
        $widgetInstance = $this->_initWidgetInstance();
        $edit = null;
        if ($edit = $this->getRequest()->getParam('m-edit')) {
            $edit = json_decode(base64_decode($edit), true);
            Mage::helper('mana_admin')->processPendingEdits('manapro_slider/product', $edit);
        }

        // layout
        $this->addActionLayoutHandles();
        $this->loadLayoutUpdates();
        $this->generateLayoutXml()->generateLayoutBlocks();
        $this->_isLayoutLoaded = true;

        try {
            Mage::helper('mana_admin')->dispatchGridAction('manapro_slider/product', $this, $edit);
        }
        catch (Mage_Core_exception $e) {
            $this->getResponse()->setBody(json_encode(array('error' => true, 'message' => $e->getMessage())));
            return;
        }

        $this->getLayout()->getBlock('m_slider_product_grid')->setEdit($edit);

        // render AJAX result
        $this->renderLayout();
    }
    public function cmsBlockGridAction() {
        $widgetInstance = $this->_initWidgetInstance();
        $edit = null;
        if ($edit = $this->getRequest()->getParam('m-edit')) {
            $edit = json_decode(base64_decode($edit), true);
            Mage::helper('mana_admin')->processPendingEdits('manapro_slider/cmsblock', $edit);
        }

        // layout
        $this->addActionLayoutHandles();
        $this->loadLayoutUpdates();
        $this->generateLayoutXml()->generateLayoutBlocks();
        $this->_isLayoutLoaded = true;

        try {
            Mage::helper('mana_admin')->dispatchGridAction('manapro_slider/cmsblock', $this, $edit);
        }
        catch (Mage_Core_exception $e) {
            $this->getResponse()->setBody(json_encode(array('error' => true, 'message' => $e->getMessage())));
            return;
        }

        $this->getLayout()->getBlock('m_slider_cmsblock_grid')->setEdit($edit);

        // render AJAX result
        $this->renderLayout();
    }
    public function htmlBlockGridAction() {
        $widgetInstance = $this->_initWidgetInstance();
        $edit = null;
        if ($edit = $this->getRequest()->getParam('m-edit')) {
            $edit = json_decode(base64_decode($edit), true);
            Mage::helper('mana_admin')->processPendingEdits('manapro_slider/htmlblock', $edit);
        }

        // layout
        $this->addActionLayoutHandles();
        $this->loadLayoutUpdates();
        $this->generateLayoutXml()->generateLayoutBlocks();
        $this->_isLayoutLoaded = true;

        try {
            Mage::helper('mana_admin')->dispatchGridAction('manapro_slider/htmlblock', $this, $edit);
        } catch (Mage_Core_exception $e) {
            $this->getResponse()->setBody(json_encode(array('error' => true, 'message' => $e->getMessage())));
            return;
        }

        $this->getLayout()->getBlock('m_slider_htmlblock_grid')->setEdit($edit);

        // render AJAX result
        $this->renderLayout();
    }
    public function chooseProductsAction() {
        $widgetInstance = $this->_initWidgetInstance();
        $edit = null;
        if ($edit = $this->getRequest()->getParam('m-edit')) {
            $edit = json_decode(base64_decode($edit), true);
            Mage::register('m_current_edit', $edit);
        }
        $this->getResponse()->setBody(Mage::helper('mana_admin')->getProductChooserHtml(array($this, '_filterProductChooserCollection')));
    }
    public function chooseCmsBlocksAction() {
        $widgetInstance = $this->_initWidgetInstance();
        $edit = null;
        if ($edit = $this->getRequest()->getParam('m-edit')) {
            $edit = json_decode(base64_decode($edit), true);
            Mage::register('m_current_edit', $edit);
        }
        $this->getResponse()->setBody(Mage::helper('mana_admin')->getCmsBlockChooserHtml(array($this, '_filterCmsBlockChooserCollection')));
    }
    /**
     * @param Mage_Adminhtml_Block_Catalog_Product_Widget_Chooser $productGrid
     * @param Mage_Adminhtml_Block_Catalog_Category_Widget_Chooser $categoryTree
     */
    public function _filterProductChooserCollection($productGrid, $categoryTree = null) {
        $edit = Mage::registry('m_current_edit');
        /* @var $collection ManaPro_Slider_Resource_Product_Collection */
        $collection = Mage::getResourceModel('manapro_slider/product_collection');
        $productGrid->setHiddenProducts(implode(',', $collection->getSelectedIds($edit['sessionId'])));
    }
    public function _filterCmsBlockChooserCollection($cmsBlockGrid, $categoryTree = null) {
        $edit = Mage::registry('m_current_edit');
        /* @var $collection ManaPro_Slider_Resource_Cmsblock_Collection */
        $collection = Mage::getResourceModel('manapro_slider/cmsblock_collection');
        $cmsBlockGrid->setHiddenBlocks(implode(',', $collection->getSelectedIds($edit['sessionId'])));
    }
    /**
     * Based on Mana_Admin_Helper_Data::addGridAction
     * @param string $type
     * @param array $edit
     * @param array $ids
     */
    public function addProductsGridAction($type, &$edit, $ids) {
        /* @var $admin Mana_Admin_Helper_Data */
        $admin = Mage::helper(strtolower('Mana_Admin'));
        foreach ($ids as $id) {
            /* @var $model ManaPro_Slider_Model_Product */
            $model = $admin->loadModel($type);
            $model
                ->setEditStatus(-1)
                ->setEditSessionId($edit['sessionId'])
                ->assignDefaultValues()
                ->setProductId($id)
                ->save();
            $edit['saved'][$model->getId()] = $model->getId();
        }
    }
    /**
     * Based on Mana_Admin_Helper_Data::addGridAction
     * @param string $type
     * @param array $edit
     * @param array $ids
     */
    public function addCmsBlocksGridAction($type, &$edit, $ids) {
        /* @var $admin Mana_Admin_Helper_Data */
        $admin = Mage::helper(strtolower('Mana_Admin'));
        foreach ($ids as $id) {
            /* @var $model ManaPro_Slider_Model_Product */
            $model = $admin->loadModel($type);
            $model
                    ->setEditStatus(-1)
                    ->setEditSessionId($edit['sessionId'])
                    ->assignDefaultValues()
                    ->setBlockId($id)
                    ->save();
            $edit['saved'][$model->getId()] = $model->getId();
        }
    }
}