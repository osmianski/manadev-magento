<?php
/**
 * @category    Mana
 * @package     ManaPro_Video
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_Video_ProductController extends Mage_Core_Controller_Front_Action {
    public function popupAction() {
        $outputStarted = false;
        $response = new Varien_Object();
        try {
            $productId = (int)$this->getRequest()->getParam('id');

            if (!$productId) {
                return false;
            }

            $product = Mage::getModel('catalog/product')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load($productId);

            Mage::register('product', $product);

            // layout
            $this->addActionLayoutHandles();
            $this->loadLayoutUpdates();
            $this->generateLayoutXml()->generateLayoutBlocks();
            $this->_isLayoutLoaded = true;

            // render AJAX result
            //ob_start();
            //$outputStarted = true;
            //$this->renderLayout();
            $response->setHtml($this->getLayout()->setDirectOutput(false)->getOutput());
            $outputStarted = false;
        }
        catch (Exception $e) {
            if ($outputStarted) {
                //ob_clean();
            }
            $response->setError($e->getMessage());
        }
        $this->getResponse()->setBody($response->toJson());
    }
}