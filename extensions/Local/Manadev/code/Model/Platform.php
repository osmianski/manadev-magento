<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Model_Platform extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Option values
     */
    const VALUE_MAGENTO_1 = 1;
    const VALUE_MAGENTO_2 = 2;


    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAllOptions() {
        if (is_null($this->_options)) {
            $this->_options = array(
                array (
                    'label' => '',
                    'value' => '',
                ),
                array(
                    'label' => Mage::helper('local_manadev')->__('Magento 1'),
                    'value' => self::VALUE_MAGENTO_1
                ),
                array(
                    'label' => Mage::helper('eav')->__('Magento 2'),
                    'value' => self::VALUE_MAGENTO_2
                ),
            );
        }

        return $this->_options;
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray() {
        $_options = array();
        foreach ($this->getAllOptions() as $option) {
            $_options[$option['value']] = $option['label'];
        }

        return $_options;
    }
}