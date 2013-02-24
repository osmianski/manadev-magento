<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterShowMore
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_FilterShowMore_Model_Source_Method extends Mana_Core_Model_Source_Abstract {
    protected function _getAllOptions() {
        return array(
            array('value' => '', 'label' => Mage::helper('manapro_filtershowmore')->__("'Show More' and 'Show Less' actions")),
            array('value' => 'scrollbar', 'label' => Mage::helper('manapro_filtershowmore')->__('Scroll bar')),
            array('value' => 'popup', 'label' => Mage::helper('manapro_filtershowmore')->__("'Show More' popup")),
        );
    }
}