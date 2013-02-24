<?php
/**
 * @category    Mana
 * @package     Mana_AttributePage
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * This class observes certain (defined in etc/config.xml) events in the whole system and provides public methods -
 * handlers for these events.
 * @author Mana Team
 *
 */
class Mana_AttributePage_Model_Observer {
    protected $_areDynamicMenuItemsLoaded = false;
    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "controller_action_layout_generate_blocks_after")
     * @param Varien_Event_Observer $observer
     */
    public function loadDynamicMenuItems($observer) {
        if (!$this->_areDynamicMenuItemsLoaded) {
            $xml = <<<EOF
<config>
    <menu>
        <mana>
            <children>
                <shopby>
                    <children>
                        <attr_manufacturer>
                            <title>Manufacturer</title>
                            <action>adminhtml/mana_attributepage/edit/id/1</action>
                            <sort_order>0</sort_order>
                        </attr_manufacturer>
                        <attr_country>
                            <title>Country of Origin</title>
                            <action>adminhtml/mana_attributepage/edit/id/2</action>
                            <sort_order>1</sort_order>
                        </attr_country>
                    </children>
                </shopby>
            </children>
        </mana>
    </menu>
</config>
EOF;
            /* @var $config Mage_Core_Model_Config_Base */
            $config = Mage::getSingleton('admin/config')->getAdminhtmlConfig();
            $dynamicConfig = new Mage_Core_Model_Config_Base();
            $dynamicConfig->loadString($xml);
            $config->extend($dynamicConfig, true);
            $this->_areDynamicMenuItemsLoaded = true;
        }
    }
}