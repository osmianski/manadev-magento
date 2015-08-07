<?php
/** 
 * @category    Mana
 * @package     ManaPro_Content
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_Content_Adminhtml_ManaPro_Content_BookController extends Mana_Admin_Controller_V2_Controller {

    public function relatedProductGridAction() {
        $id = $this->getRequest()->getPost('id');
        if (substr($id, 0, 1) == "n") {
            $id = null;
        }
        $models = $this->contentHelper()->registerModels($id);
        $ids = $this->getRequest()->getParam('related_product_ids');
        $this->contentProHelper()->processRelatedProductIds($ids);
        $this->loadLayout();
        $this->renderLayout();
    }

    public function relatedProductGridSelectionAction() {
        $ids = $this->getRequest()->getParam('changes_related_products');
        $this->contentProHelper()->processRelatedProductIds($ids);
        $this->getResponse()->setBody(Mage::helper('mana_admin')->getProductChooserHtml(array($this, '_filterProductChooserCollection')));
    }

    public function _filterProductChooserCollection($productGrid, $categoryTree = null) {
        $productGrid->setHiddenProducts(implode(',', Mage::registry('related_product_ids')));
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('admin/mana/contentpage');
    }

    #region Dependencies
    /**
     * @return Mana_Content_Helper_Data
     */
    public function contentHelper() {
        return Mage::helper('mana_content');
    }

    /**
     * @return ManaPro_Content_Helper_Data
     */
    public function contentProHelper() {
        return Mage::helper('manapro_content');
    }
    #endregion
}