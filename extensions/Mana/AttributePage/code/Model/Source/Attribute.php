<?php
/**
 * @category    Mana
 * @package     Mana_AttributePage
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_AttributePage_Model_Source_Attribute extends Mana_Core_Model_Source_Abstract {
    protected function _getAllOptions() {
        $result = array(array('value' => '', 'label' => ''));
        $data = $this->getAttributeResource()->getAttributes(Mana_AttributePage_Resource_Attribute::FIELDS_LABEL);
        foreach ($data as $value => $label) {
            $result[] = array('value' => $value, 'label' => $label);
        }

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
     * @return Mana_AttributePage_Resource_Attribute
     */
    public function getAttributeResource() {
        return Mage::getResourceSingleton('mana_attributepage/attribute');
    }
    #endregion
}