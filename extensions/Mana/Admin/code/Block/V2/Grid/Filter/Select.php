<?php
/**
 * @category    Mana
 * @package     Mana_Admin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Admin_Block_V2_Grid_Filter_Select extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select {
    protected function _getOptions() {
        $emptyOption = array('value' => null, 'label' => '');

        $optionGroups = $this->getColumn()->getOptionGroups();
        if ($optionGroups) {
            array_unshift($optionGroups, $emptyOption);

            return $optionGroups;
        }

        $colOptions = $this->getColumn()->getOptions();
        if (is_string($colOptions)) {
            /* @var $optionSource Mana_Core_Model_Source_Abstract */
            $optionSource = Mage::getModel($colOptions);
            $colOptions = $optionSource->getOptionArray();
        }
        if (!empty($colOptions) && is_array($colOptions)) {
            $options = isset($colOptions['']) ? array() : array($emptyOption);
            foreach ($colOptions as $value => $label) {
                $options[] = array('value' => $value, 'label' => $label);
            }

            return $options;
        }

        return array();
    }
}