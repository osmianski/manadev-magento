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
abstract class ManaPro_Slider_Block_Abstract extends Mage_Core_Block_Template {
    public function init() {
        $this->setTemplate((string)$this->getDisplayOptions()->template);
        return $this;
    }
    public function getPosition() {
        return ManaPro_Slider_Block_Slider::DEFAULT_POSITION;
    }

    abstract public function getChildName();
}