<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterSeoLinks
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_FilterSeoLinks_Model_Source_Noindex extends Mana_Core_Model_Source_Abstract {
    protected function _getAllOptions() {
        /* @var $core Mana_Core_Helper_Data */ $core = Mage::helper(strtolower('Mana_Core'));
        $result = array();

        foreach ($core->getSortedXmlChildren(Mage::getConfig()->getNode('manapro_filterseolinks'), 'noindex') as $key => $options) {
            $module = isset($options['module']) ? ((string)$options['module']) : 'manapro_filterseolinks';
            $result[] = array('label' => Mage::helper($module)->__((string)$options->title), 'value' => $key);
        }
        return $result;
    }
    public function toOptionArray() {
        return $this->_getAllOptions();
    }
    // temp
}