<?php
/** 
 * @category    Mana
 * @package     ManaSlider_Tabbed
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaSlider_Tabbed_TabbedSliderController extends Mage_Core_Controller_Front_Action {
    public function productsAction() {
        // layout
        $this->addActionLayoutHandles();
        $this->loadLayoutUpdates();
        $this->generateLayoutXml()->generateLayoutBlocks();
        $this->_isLayoutLoaded = true;

        if ($block = $this->getLayout()->getBlock('product_slider')) {
            /* @var $block ManaSlider_Tabbed_Block_ProductSlider */
            $xml = base64_decode($this->getRequest()->getPost('xml'));
            $block->prepare(simplexml_load_string($xml), true);
            $this->getResponse()->appendBody($block->getChildHtml('product_list'));
        }
    }
}