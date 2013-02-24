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
abstract class ManaPro_Slider_Model_Source_Navigation_Abstract extends Mana_Core_Model_Source_Abstract {
    protected $_rootNode;
    protected $_childNode;
    protected $_defaultTranslationModule;

    protected function _getAllOptions() {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper(strtolower('Mana_Core'));
        $result = array();

        foreach ($core->getSortedXmlChildren(Mage::getConfig()->getNode($this->_rootNode), $this->_childNode) as $key => $options) {
            $module = isset($options['module']) ? ((string)$options['module']) : $this->_defaultTranslationModule;
            $result[] = array('label' => Mage::helper($module)->__((string)$options->title), 'value' => $key);
        }
        return array_merge($result, array(
            array('value' => 'none', 'label' => Mage::helper('manapro_slider')->__('None (Hide)')),
        ));
    }
}