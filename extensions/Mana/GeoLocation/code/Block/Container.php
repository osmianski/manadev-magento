<?php
/**
 * @category    Mana
 * @package     Mana_GeoLocation
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_GeoLocation_Block_Container extends Mage_Adminhtml_Block_Widget_View_Container {
    public function __construct() {
        parent::__construct();

        $this->_removeButton('back');
        $this->_removeButton('edit');
        $this->_removeButton('save');
        $this->_removeButton('delete');
    }

    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return string
     */
    public function getHeaderText() {
        return $this->__('Geo Location');
    }

    protected function _prepareLayout() {
        return $this;
    }

    public function getViewHtml() {
        return '<div id="view"></div>';
    }

}