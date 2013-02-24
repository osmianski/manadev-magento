<?php
/**
 * @category    Mana
 * @package     ManaPro_Featured
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_Featured_Model_Effect_Blind {
    public function getEffect($effect) {
        return $effect;
    }
    public function getEffectOptions($effect, $configSource, $op) {
        return array(
            'direction' => Mage::getStoreConfig($configSource . '_carousel_effect/' . $op .'_'. $effect.'_direction'),
        );
    }
}