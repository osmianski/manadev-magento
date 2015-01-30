<?php
/**
 * @category    Mana
 * @package     Mana_Sorting
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Sorting_Model_Source_Attribute extends Mana_Core_Model_Source_Abstract {
    protected function _getAllOptions() {
        $result = array(array('value' => '', 'label' => ''));
        $data = $this->getAttributeResource()->getAttributes();
        $sortingMethods = array();
        $sortableAttributes = array();
        $otherAttributes = array();
        foreach ($data as $value => $columns) {
            $label = $columns['label'];
            if($columns['used_for_sort_by'] == "1") {
                $sortableAttributes[] = array('value' => $value, 'label' => $label);
            } else {
                $otherAttributes[] = array('value' => $value, 'label' => $label);
            }
        }
        foreach ($this->sortingHelper()->getSortingMethodXmls(false) as $code => $xml) {
            $sortingMethods[] = array('label' => (string)$xml->label, 'value' => $code);
        }
        $result[] = array('label' => 'Sorting Methods', 'value' => $sortingMethods);
        $result[] = array('label' => 'Sortable Attributes', 'value' => $sortableAttributes);
        $result[] = array('label' => 'Other Attributes', 'value' => $otherAttributes);


        return $result;
    }

    #region Dependencies
    /**
     * @return Mana_Core_Helper_Data
     */
    public function coreHelper() {
        return Mage::helper('mana_core');
    }
    /**
     * @return Mana_Admin_Helper_Data
     */
    public function adminHelper() {
        return Mage::helper('mana_admin');
    }
    /**
     * @return Mana_Sorting_Resource_Source_Attribute
     */
    public function getAttributeResource() {
        return Mage::getResourceSingleton('mana_sorting/source_attribute');
    }
    /**
     * @return Mana_Sorting_Helper_Data
     */
    public function sortingHelper() {
        return Mage::helper('mana_sorting');
    }
    #endregion
}