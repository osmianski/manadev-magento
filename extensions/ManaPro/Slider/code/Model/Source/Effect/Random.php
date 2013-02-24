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
class ManaPro_Slider_Model_Source_Effect_Random extends ManaPro_Slider_Model_Source_Effect_Abstract {
    protected $_filter = 'random';
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
        return $result;
    }
//    public function toOptionArray() {
//        return $this->_getAllOptions();
//    }
}