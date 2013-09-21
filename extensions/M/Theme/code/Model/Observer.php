<?php
/**
 * @category    Mana
 * @package     M_Theme
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * This class observes certain (defined in etc/config.xml) events in the whole system and provides public methods -
 * handlers for these events.
 * @author Mana Team
 *
 */
class M_Theme_Model_Observer
{
    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "core_block_abstract_to_html_before")
     * @param Varien_Event_Observer $observer
     */
    public function setTemplate($observer) {
        /* @var $block Mage_Core_Block_Abstract */
        $block = $observer->getEvent()->getBlock();
    	/* @var $theme M_Theme_Helper_Data */
    	$theme = Mage::helper(strtolower('M_Theme'));
    	$config = $theme->getConfig()->getNode();
    	$class = get_class($block);

        if ($block instanceof Mage_Catalog_Block_Product_List) {
            /* @var $t M_Theme_Helper_Data */
            $t = Mage::helper(strtolower('M_Theme'));
            $config = $t->getConfig()->getNode();
            $mode = $block->getToolbarBlock()->getCurrentMode();
            if (isset($config->catalog->product->list_modes->$mode->template)) {
                $block->setTemplate((string)$config->catalog->product->list_modes->$mode->template);
            }
        }
        if (!empty($config->system->no_cache->class->$class)) {
            $block->setCacheLifetime(null);
        }
    }
}