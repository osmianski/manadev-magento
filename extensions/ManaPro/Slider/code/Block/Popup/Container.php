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
class ManaPro_Slider_Block_Popup_Container extends Mage_Adminhtml_Block_Template {
    protected function _construct() {
        parent::_construct();
        $this->setTemplate('manapro/slider/popup.phtml');
    }
}