<?php
/**
 * @category    Mana
 * @package     ManaPro_Slider
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_Slider_Model_Source_Effect_Abstract extends Mana_Core_Model_Source_Abstract {
    protected $_filter = '';
    protected function _getAllOptions() {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper(strtolower('Mana_Core'));
        $result = array();

        foreach ($core->getSortedXmlChildren(Mage::getConfig()->getNode('manapro_slider'), 'effect') as $key => $options) {
            if (!($filter = $this->_filter) || !empty($options->$filter)) {
                $module = isset($options['module']) ? ((string)$options['module']) : 'manapro_slider';
                $result[] = array('label' => Mage::helper($module)->__((string)$options->title), 'value' => $key);
            }
        }
        return array_merge($result, array(
            array('value' => 'random', 'label' => Mage::helper('manapro_slider')->__('Change Effects Randomly')),
            array('value' => 'none', 'label' => Mage::helper('manapro_slider')->__('None')),
        ));
    }
}