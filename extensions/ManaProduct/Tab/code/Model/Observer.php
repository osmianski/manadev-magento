<?php
/**
 * @category    Mana
 * @package     ManaProduct_Tab
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * This class observes certain (defined in etc/config.xml) events in the whole system and provides public methods -
 * handlers for these events.
 * @author Mana Team
 *
 */
class ManaProduct_Tab_Model_Observer {
    protected $_isDynamicConfigurationLoaded = false;
    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "controller_action_layout_generate_blocks_after")
     * @param Varien_Event_Observer $observer
     */
    public function loadDynamicSystemConfiguration($observer) {
        /* @var $core Mana_Core_Helper_Data */ $core = Mage::helper(strtolower('Mana_Core'));
        if ($core->getRoutePath() == 'adminhtml/system_config/edit' && !$this->_isDynamicConfigurationLoaded) {
            /* @var $sections Mage_Core_Model_Config_Element */
            $sections = Mage::getSingleton('adminhtml/config')->getSections();
            $dynamicConfig = Mage::getConfig()->loadModulesConfiguration('manaproduct_tab_system.xml');
            $sections->extend($dynamicConfig->getNode('sections'), true);
            $this->_isDynamicConfigurationLoaded = true;
        }
    }
}