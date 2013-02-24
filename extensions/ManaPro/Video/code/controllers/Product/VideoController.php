<?php
/**
 * @category    Mana
 * @package     ManaPro_Video
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

require_once  BP . DS . 'app' . DS . 'code' . DS . 'core' . DS . 'Mage' . DS . 'Adminhtml' . DS . 'controllers' . DS . 'Catalog' . DS . 'ProductController.php';

/**
 * @author Mana Team
 *
 */
class ManaPro_Video_Product_VideoController extends Mage_Adminhtml_Catalog_ProductController {
    /**
     * AJAX action, renders initial tab markup
     */
    public function tabAction() {
        $product = $this->_initProduct();
        Mage::register('m_crud_model', $product);

        // layout
        $this->addActionLayoutHandles();
        $this->loadLayoutUpdates();
        $this->generateLayoutXml()->generateLayoutBlocks();
        $this->_isLayoutLoaded = true;

        // render AJAX result
        $this->renderLayout();
    }
    public function gridAction() {
        $product = $this->_initProduct();
        Mage::register('m_crud_model', $product);
        $edit = null;
        if ($edit = $this->getRequest()->getParam('m-edit')) {
            $edit = json_decode(base64_decode($edit), true);
            Mage::helper('mana_admin')->processPendingEdits('manapro_video/video', $edit);
        }

        // layout
        $this->addActionLayoutHandles();
        $this->loadLayoutUpdates();
        $this->generateLayoutXml()->generateLayoutBlocks();
        $this->_isLayoutLoaded = true;

        try {
            Mage::helper('mana_admin')->dispatchGridAction('manapro_video/video', $this, $edit);
        }
        catch (Mage_Core_exception $e) {
            $this->getResponse()->setBody(json_encode(array('error' => true, 'message' => $e->getMessage())));
            return;
        }

        $this->getLayout()->getBlock('m_video_grid')->setEdit($edit);

        // render AJAX result
        $this->renderLayout();
    }
}