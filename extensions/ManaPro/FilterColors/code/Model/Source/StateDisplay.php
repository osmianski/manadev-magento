<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterColors
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_FilterColors_Model_Source_StateDisplay extends Mana_Core_Model_Source_Abstract {
    protected function _getAllOptions() {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper(strtolower('Mana_Core'));
        $result = array(
            array('label' => Mage::helper('manapro_filtercolors')->__('As In Filter Options'), 'value' => '')
        );

        foreach ($core->getSortedXmlChildren(Mage::getConfig()->getNode('mana_filters'), 'color_state_display') as $key => $options) {
            $module = isset($options['module']) ? ((string)$options['module']) : 'manapro_filtercolors';
            $result[] = array('label' => Mage::helper($module)->__((string)$options->title), 'value' => $key);
        }

        return $result;
    }
}