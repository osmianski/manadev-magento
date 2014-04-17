<?php

class Local_Manadev_Block_Product_Widget_New extends Mage_Catalog_Block_Product_Widget_New {
    protected function _construct() {
        parent::_construct();

        $this->unsetData('cache_lifetime');
    }

}