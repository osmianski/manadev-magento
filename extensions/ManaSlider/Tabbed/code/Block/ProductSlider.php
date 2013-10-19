<?php
/** 
 * @category    Mana
 * @package     ManaSlider_Tabbed
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 * @method ManaSlider_Tabbed_Model_Tab[] getTabs()
 * @method int getHeight()
 */
class ManaSlider_Tabbed_Block_ProductSlider extends Mage_Catalog_Block_Product_Abstract
{
    protected function _construct() {
        parent::_construct();
        $this->setTemplate('manaslider/tabbed/product-slider.phtml');
    }
}