<?php
/**
 * @category    Mana
 * @package     Mana_Theme
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Theme_Model_Source_Buttonicon extends Mana_Core_Model_Source_Abstract
{
    const HIDE = 0;
    const BUTTON = 1;
    const ICON = 2;
    protected function _getAllOptions()
    {
        return array(
            array(
                'value' => self::HIDE,
                'label' => Mage::helper('mana_theme')->__('No')
            ),
            array(
                'value' => self::BUTTON,
                'label' => Mage::helper('mana_theme')->__('Yes, As Button')
            ),
            array(
                'value' => self::ICON,
                'label' => Mage::helper('mana_theme')->__('Yes, As Icon')
            ),
        );
    }
}
