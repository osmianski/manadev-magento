<?php
/**
 * @category    Mana
 * @package     ManaPro_SuperProductName
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * This class observes certain (defined in etc/config.xml) events in the whole system and provides public methods -
 * handlers for these events.
 * @author Mana Team
 *
 */
class ManaPro_SuperProductName_Model_Observer {
    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "core_block_abstract_to_html_after")
     * @param Varien_Event_Observer $observer
     */
    public function renderSuperProductName($observer) {
        /* @var $block Mage_Core_Block_Abstract */
        $block = $observer->getEvent()->getBlock();
        /* @var $transport Varien_Object */
        $transport = $observer->getEvent()->getTransport();

        /* @var $nameBlock Mage_Core_Block_Abstract */
        if (($nameBlock = $this->_getNameBlock($block->getLayout())) && $block == $nameBlock->getParentBlock()) {
            $productHtml = $transport->getHtml();
            $product = $nameBlock->getProduct();
            $defaultNameHtml = Mage::helper('manapro_superproductname')->formatProductName($product);
            if ($product->getNameWysiwyg() && $product->getNameWysiwyg() != $defaultNameHtml) {
                $nameHtml = $nameBlock->toHtml();
                $productHtml = str_replace($defaultNameHtml, $nameHtml, $productHtml);
            }
            $transport->setHtml($productHtml);
        }
    }

    protected $_nameBlock;
    protected $_nameBlockInitialized = false;
    /**
     * @param Mage_Core_Model_Layout $layout
     */
    protected function _getNameBlock($layout) {
        if (!$this->_nameBlockInitialized) {
            $this->_nameBlockInitialized = true;
            $this->_nameBlock = $layout->getBlock('product.info.m_supername');
        }
        return $this->_nameBlock;
    }

    public function renderSuperProductNameOptionsInBackend() {
        Mage::helper('mana_core/js')->options('#name_wysiwyg', array(
            'template' => Mage::helper('manapro_superproductname')->formatProductName(),
        ));
    }
}