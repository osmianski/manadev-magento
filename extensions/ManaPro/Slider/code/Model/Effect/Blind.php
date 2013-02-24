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
class ManaPro_Slider_Model_Effect_Blind {
    public function getEffect($effect) {
        return $effect;
    }
    public function getEffectOptions($effect, $slider, $op) {
        return array(
            'direction' => $slider->getData(sprintf('effect_%s_%s_direction', $op, $effect)),
        );
    }
}