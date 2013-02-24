<?php
/**
 * @category    Mana
 * @package     ManaPro_Video
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_Video_Model_Source_Media extends Mana_Core_Model_Source_Abstract {
    protected function _getAllOptions() {
        return array(
            array('value' => 'all', 'label' => mage::helper('manapro_video')->__('Show Images and Videos as One List')),
            array('value' => 'tabs', 'label' => mage::helper('manapro_video')->__('Show Images and Videos in Tabs')),
        );
    }
}