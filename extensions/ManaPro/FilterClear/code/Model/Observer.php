<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterClear
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * This class observes certain (defined in etc/config.xml) events in the whole system and provides public methods -
 * handlers for these events.
 * @author Mana Team
 *
 */
class ManaPro_FilterClear_Model_Observer {
    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "m_advanced_filter_name_action")
     * @param Varien_Event_Observer $observer
     */
    public function renderAction($observer) {
        /* @var $block Mana_Filters_Block_View */ $block = $observer->getEvent()->getBlock();
        /* @var $filter Mana_Filters_Block_Filter */ $filter = $observer->getEvent()->getFilter();
    	/* @var $layout Mage_Core_Model_Layout */ $layout = Mage::getSingleton('core/layout');
    	/* @var $result Varien_Object */ $result = $observer->getEvent()->getResult();
    	if ($helperBlock = $layout->getBlock('m_filter_clear')) {
            if ($html = trim($helperBlock->setFilter($filter)->toHtml())) {
                $actions = $result->getResult();
                $actions[] = array('html' => $html, 'position' => 100);
                $result->setResult($actions);
            }
        }
    }

    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "m_advanced_filter_horizontal_name_before",
     * "m_advanced_filter_menu_name_before")
     * @param Varien_Event_Observer $observer
     */
    public function render($observer) {
        /* @var $block Mana_Filters_Block_View */ $block = $observer->getEvent()->getBlock();
        /* @var $filter Mana_Filters_Block_Filter */ $filter = $observer->getEvent()->getFilter();
    	/* @var $layout Mage_Core_Model_Layout */ $layout = Mage::getSingleton('core/layout');
    	if ($helperBlock = $layout->getBlock('m_filter_clear')) {
            echo $helperBlock->setFilter($filter)->toHtml();
        }
    }
}