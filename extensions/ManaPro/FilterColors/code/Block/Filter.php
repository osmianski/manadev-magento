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
class ManaPro_FilterColors_Block_Filter extends Mana_Filters_Block_Filter {
    public function getFilterClass() {
        /* @var $colors ManaPro_FilterColors_Helper_Data */ $colors = Mage::helper(strtolower('ManaPro_FilterColors'));
        return $colors->getFilterClass($this->getFilterOptions());
    }
    public function getFilterValueClass($item) {
        /* @var $colors ManaPro_FilterColors_Helper_Data */ $colors = Mage::helper(strtolower('ManaPro_FilterColors'));
        return $colors->getFilterValueClass($this->getFilterOptions(), $item->getValue());
    }

    protected function _prepareLayout() {
        /* @var $files Mana_Core_Helper_Files */ $files = Mage::helper(strtolower('Mana_Core/Files'));
        /* @var $colors ManaPro_FilterColors_Helper_Data */ $colors = Mage::helper(strtolower('ManaPro_FilterColors'));
        if (/* @var $head Mage_Page_Block_Html_Head */ $head = $this->getLayout()->getBlock('head')) {
            $css = $head->hasMCss() ? $head->getMCss() : array();
            $url = $colors->getCssRelativeUrl($this->getFilterOptions());
            if (!in_array($url, $css)) {
                $css[] = $url;
            }
            $head->setMCss($css);
        }
        return parent::_prepareLayout();
    }

}