<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterColors
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_FilterColors_Block_State extends Mage_Core_Block_Template {
    public function __construct() {
        parent::__construct();
        $this->setTemplate('manapro/filtercolors/state.phtml');
    }
    public function getFilterValueClass($item) {
        /* @var $colors ManaPro_FilterColors_Helper_Data */ $colors = Mage::helper(strtolower('ManaPro_FilterColors'));
        return $colors->getFilterValueClass($this->getFilterOptions(), $item->getValue());
    }

}