<?php
/**
 * @category    Mana
 * @package     Mana_Social
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Social_Model_Source_Facebook_Color extends Mana_Core_Model_Source_Abstract
{
    protected function _getAllOptions()
    {
        /* @var $t Mana_Social_Helper_Data */
        $t = Mage::helper('mana_social');

        return array(
            array('value' => 'light', 'label' => $t->__('Light')),
            array('value' => 'dark', 'label' => $t->__('Dark')),
        );
    }
}