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
class Mana_GeoLocation_Block_Import extends Mage_Adminhtml_Block_Widget
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected function _construct() {
        parent::_construct();
        $this->setTemplate('mana/geolocation/import.phtml');
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel() {
        return $this->__('Update IP Database');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle() {
        return $this->__('Find Country By IP');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return true
     */
    public function canShowTab() {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden() {
        return false;
    }
}