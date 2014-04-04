<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterContent
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * This class observes certain (defined in etc/config.xml) events in the whole system and provides public methods - 
 * handlers for these events.
 * @author Mana Team
 *
 */
class ManaPro_FilterContent_Model_Observer {
    #region Initial Meta Data
    /**
     * @param Mage_Page_Block_Html_Head $head
     * @return string
     */
    protected function _getInitialTitle($head) {
        $title = $head->getData('title');
        if (($prefix = Mage::getStoreConfig('design/head/title_prefix')) && $this->coreHelper()->startsWith($title, $prefix)) {
            $title = substr($title, strlen($prefix) + 1);
        }
        if (($suffix = Mage::getStoreConfig('design/head/title_suffix')) && $this->coreHelper()->endsWith($title, $suffix)) {
            $title = substr($title, 0, strlen($title) - strlen($suffix) - 1);
        }

        return trim($title);
    }

    /**
     * @param Mage_Page_Block_Html_Head $head
     * @return string
     */
    protected function _getInitialKeywords($head) {
        $result = $head->getData('keywords');

        return $result;
    }

    /**
     * @param Mage_Page_Block_Html_Head $head
     * @return string
     */
    protected function _getInitialDescription($head) {
        $result = $head->getData('description');

        return $result;
    }
    #endregion

    /**
     * Handles event "controller_action_layout_generate_blocks_after".
     * @param Varien_Event_Observer $observer
     */
    public function addCustomContentToGeneratedBlocks($observer) {
        /* @var $layout Mage_Core_Model_Layout */
        $layout = $observer->getEvent()->getData('layout');

        if ($head = $layout->getBlock('head')) {
            /* @var $head Mage_Page_Block_Html_Head */

            $this->metaTitleContentHelper()->replaceInitialContent($this->_getInitialTitle($head));
            $this->metaKeywordsContentHelper()->replaceInitialContent($this->_getInitialKeywords($head));
            $this->metaDescriptionContentHelper()->replaceInitialContent($this->_getInitialDescription($head));
            if (($title = $this->metaTitleContentHelper()->render()) !== false) {
                $head->setTitle($title);
            }
            if (($keywords = $this->metaKeywordsContentHelper()->render()) !== false) {
                $head->setData('keywords', $keywords);
            }
            if (($description = $this->metaDescriptionContentHelper()->render()) !== false) {
                $head->setData('description', $description);
            }
        }
    }

    /**
     * Handles event "controller_action_layout_generate_xml_before".
     * @param Varien_Event_Observer $observer
     */
    public function addCustomContentToLayoutXml($observer) {
        /* @var $layout Mage_Core_Model_Layout */
        $layout = $observer->getEvent()->getData('layout');

        if ($layoutUpdate = $this->layoutXmlContentHelper()->render(null)) {
            $layout->getUpdate()->addUpdate($layoutUpdate);
        }
        if ($layoutUpdate = $this->widgetLayoutXmlContentHelper()->render(null)) {
            $layout->getUpdate()->addUpdate($layoutUpdate);
        }
    }

    /**
     * Handles event "core_block_abstract_to_html_before".
     * @param Varien_Event_Observer $observer
     */
    public function addCustomContentToBlockBeforeRendering($observer) {
        /* @var $block Mage_Core_Block_Abstract */
        $block = $observer->getEvent()->getBlock();
        foreach ($block->getData() as $key => $value) {
            if ($helper = $this->factoryHelper()->createBlockHelper($key, $value)) {
                $helper->before($block, $key);
            }
        }
    }

    /**
     * Handles event "core_block_abstract_to_html_after".
     * @param Varien_Event_Observer $observer
     */
    public function restoreOriginalBlockContentAfterRenderingAndPostProcess($observer) {
        /* @var $block Mage_Core_Block_Abstract */
        $block = $observer->getEvent()->getData('block');

        /* @var $transport Varien_Object */
        $htmlObject = $observer->getEvent()->getData('transport');

        foreach ($block->getData() as $key => $value) {
            if ($helper = $this->factoryHelper()->createBlockHelper($key, $value)) {
                $helper->after($block, $key, $htmlObject);
            }
        }
    }

    #region Dependencies
    /**
     * @return ManaPro_FilterContent_Helper_Data
     */
    public function helper() {
        return Mage::helper('manapro_filtercontent');
    }

    /**
     * @return ManaPro_FilterContent_Helper_Factory
     */
    public function factoryHelper() {
        return Mage::helper('manapro_filtercontent/factory');
    }

    /**
     * @return ManaPro_FilterContent_Helper_Content
     */
    public function layoutXmlContentHelper() {
        return $this->factoryHelper()->createContentHelper('layout_xml');
    }
    /**
     * @return ManaPro_FilterContent_Helper_Content
     */
    public function widgetLayoutXmlContentHelper() {
        return $this->factoryHelper()->createContentHelper('widget_layout_xml');
    }
    /**
     * @return ManaPro_FilterContent_Helper_Content
     */
    public function metaTitleContentHelper() {
        return $this->factoryHelper()->createContentHelper('meta_title');
    }
    /**
     * @return ManaPro_FilterContent_Helper_Content
     */
    public function metaKeywordsContentHelper() {
        return $this->factoryHelper()->createContentHelper('meta_keywords');
    }
    /**
     * @return ManaPro_FilterContent_Helper_Content
     */
    public function metaDescriptionContentHelper() {
        return $this->factoryHelper()->createContentHelper('meta_description');
    }

    /**
     * @return Mana_Core_Helper_Data
     */
    public function coreHelper() {
        return Mage::helper('mana_core');
    }
    /**
     * @return Mana_Filters_Helper_Data
     */
    public function filterHelper() {
        return Mage::helper('mana_filters');
    }
    #endregion
}