<?php
/** 
 * @category    Mana
 * @package     Mana_Sorting
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * This class observes certain (defined in etc/config.xml) events in the whole system and provides public methods - 
 * handlers for these events.
 * @author Mana Team
 *
 */
class Mana_Sorting_Model_Observer {
    /**
     * Handles event "m_toolbar_orders".
     * @param Varien_Event_Observer $observer
     */
    public function addSortingMethodsToSeo($observer) {
        /* @var $obj Varien_Object */
        $obj = $observer->getEvent()->getObj();

        $orders = $obj->getData('orders');
        foreach ($this->sortingHelper()->getSortingMethodXmls() as $xml) {
            $orders[] = (string)$xml->code;
        }
        foreach($this->sortingHelper()->getCustomSortMethodCollection() as $customSortingMethod) {
            $orders[] = (string)$customSortingMethod->getUrlKey();
        }
        $obj->setData('orders', $orders);
    }
    #region Dependencies
    /**
     * @return Mana_Sorting_Helper_Data
     */
    public function sortingHelper() {
        return Mage::helper('mana_sorting');
    }
    #endregion
}