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
     * REPLACE THIS WITH DESCRIPTION (handles event "adminhtml_controller_action_predispatch_start")
     * @param Varien_Event_Observer $observer
     */
    public function loadDynamicMenuItems($observer) {
        if (!$this->_areDynamicMenuItemsLoaded) {
            try {
                $collection = Mage::getResourceModel("mana_attributepage/attributePage_global_collection")
                    ->setOrder('title', Varien_Data_Collection::SORT_ORDER_ASC);
                $xml = <<<EOF
<config>
    <menu>
        <mana>
            <children>
                <attributepage>
                    <children>
EOF;
                $i = 0;
                foreach ($collection as $attributePage)
                {
                    $i++;
                    /* @var $attributePage Mana_AttributePage_Model_AttributePage_Global */
                    $xml .= <<<EOF
                            <attr_{$attributePage->getId()}>
                                <title><![CDATA[{$this->attributePageHelper()->__('Option Pages (%s)', $attributePage->getData('raw_title'))}]]></title>
                                <action>adminhtml/mana_optionPage/index/parent_id/{$attributePage->getId()}</action>
                                <sort_order>{$i}</sort_order>
                            </attr_{$attributePage->getId()}>
EOF;
                }


    //                        <attr_country>
    //                            <title>Country of Origin</title>
    //                            <action>adminhtml/mana_attributepage/edit/id/2</action>
    //                            <sort_order>1</sort_order>
    //                        </attr_country>
                $xml .= <<<EOF
                    </children>
                </attributepage>
            </children>
        </mana>
    </menu>
	<acl>
		<resources>
			<admin>
				<children>
					<mana>
						<children>
							<attributepage>
                                <children>
EOF;
                $i = 0;
                foreach ($collection as $attributePage)
                {
                    $i++;
                    /* @var $attributePage Mana_AttributePage_Model_AttributePage_Global */
                    $xml .= <<<EOF
                            <attr_{$attributePage->getId()}>
                                <title><![CDATA[{$this->attributePageHelper()->__('Option Pages (%s)', $attributePage->getData('raw_title'))}]]></title>
                                <sort_order>{$i}</sort_order>
                            </attr_{$attributePage->getId()}>
EOF;
                }
                $xml .= <<<EOF
                                </children>
							</attributepage>
						</children>
					</mana>
                </children>
			</admin>
		</resources>
	</acl>
</config>
EOF;
                /* @var $config Mage_Core_Model_Config_Base */
                $config = Mage::getSingleton('admin/config')->getAdminhtmlConfig();
                $dynamicConfig = new Mage_Core_Model_Config_Base();
                $dynamicConfig->loadString($xml);
                $config->extend($dynamicConfig, true);
                $this->_areDynamicMenuItemsLoaded = true;
            }
            catch (Exception $e) {
                $this->_areDynamicMenuItemsLoaded = true;
            }
        }
    }

    #region Dependencies

    /**
     * @return Mana_AttributePage_Helper_Data
     */
    public function attributePageHelper() {
        return Mage::helper('mana_attributepage');
    }

    #endregion
}