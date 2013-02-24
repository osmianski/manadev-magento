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
class ManaPro_Slider_Block_Popup_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {
    /**
     * Internal constructor
     *
     */
    protected function _construct() {
        parent::_construct();
        $this->setId('m_slider_popup_tabs');
        $this->setDestElementId('m_slider_popup_form');
    }
}
