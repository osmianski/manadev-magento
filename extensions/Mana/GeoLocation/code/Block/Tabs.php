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
class Mana_GeoLocation_Block_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {
    public function __construct() {
        parent::__construct();
        $this->setId('tabs');
        $this->setDestElementId('view');
    }
}