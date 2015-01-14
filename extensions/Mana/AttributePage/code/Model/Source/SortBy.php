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
class Mana_AttributePage_Model_Source_SortBy extends Mana_Core_Model_Source_Abstract {
    /**
     * Retrieve All options
     *
     * @return array
     */
    protected function _getAllOptions()
    {
        $options = array(array(
            'label' => Mage::helper('catalog')->__('Best Value'),
            'value' => 'position'
        ));
        foreach ($this->_getCatalogConfig()->getAttributesUsedForSortBy() as $attribute) {
            $options[] = array(
                'label' => Mage::helper('catalog')->__($attribute['frontend_label']),
                'value' => $attribute['attribute_code']
            );
        }
        $core = Mage::helper('mana_core');

        if ($core->isManadevSortingInstalled()) {
            $sorting = Mage::helper('mana_sorting');
            //      $sorting->addManaSortingOptions($options);
            foreach ($sorting->getSortingMethodXmls() as $xml) {
                $options[] = array(
                    'label' => (string)$xml->label,
                    'value' => (string)$xml->code
                );
            }
        }

        return $options;
    }

    #region Dependencies
    /**
     * Retrieve Catalog Config Singleton
     *
     * @return Mage_Catalog_Model_Config
     */
    protected function _getCatalogConfig() {
        return Mage::getSingleton('catalog/config');
    }
    #endregion
}