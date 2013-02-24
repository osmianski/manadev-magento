<?php
/**
 * @category    Mana
 * @package     ManaPro_Featured
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * Generic helper functions for ManaPro_Featured module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class ManaPro_Featured_Helper_Data extends Mage_Core_Helper_Abstract {
    public function beginSmallImageDecoration($product) {
        if ($this->isFeatured($product)) {
            $result = '<div class="m-small-image-tag-container">';
            $isTagged = false;
            foreach (array('top_left', 'top', 'top_right', 'right', 'bottom_right', 'bottom', 'bottom_left', 'left') as $_position) {
                if ($_decorationCssClasses = $this->getSmallImageDecorationCssClasses($_position)) {
                    $result .= '<div class="m-tag '. $_decorationCssClasses.'"></div>';
                    $isTagged = true;
                }
            }

            return $isTagged ? $result : '';
        }
        else {
            return '';
        }
    }
    public function classSmallImageDecoration($product) {
        if ($this->isFeatured($product)) {
            return 'm-tagged-small-image';
        }
        else {
            return '';
        }
    }
    public function endSmallImageDecoration($product) {
        if ($this->isFeatured($product)) {
            $isTagged = false;
            foreach (array('top_left', 'top', 'top_right', 'right', 'bottom_right', 'bottom', 'bottom_left', 'left') as $_position) {
                if ($_decorationCssClasses = $this->getSmallImageDecorationCssClasses($_position)) {
                    $isTagged = true;
                }
            }
            return $isTagged ? '</div>' : '';
        }
        else {
            return '';
        }
    }
    protected $_todayDate;
    public function isFeatured($product) {
        if (!$this->_todayDate) {
            $this->_todayDate = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        }
        return $product->getMFeaturedFromDate() && $product->getMFeaturedFromDate() <= $this->_todayDate &&
            (!$product->getMFeaturedToDate() || $product->getMFeaturedToDate() >= $this->_todayDate);
    }
    public function getSmallImageDecorationCssClasses($position) {
        $configSource = 'mana_featured/category';
        if (($decoration = Mage::getStoreConfig($configSource . '_carousel_si_decoration/' . $position)) == 'none') {
            return false;
        }
        else {
            $css = 'm-' . str_replace('_', '-', $position) . ' ';
            if ($decoration == 'custom') {
                if ($custom = Mage::getStoreConfig($configSource . '_carousel_si_decoration/' . $position . '_custom')) {
                    $css .= $custom;
                }
                else {
                    return false;
                }
            }
            elseif ($node = Mage::getConfig()->getNode('mana_featured/carousel_decoration/' . $position . '/' . $decoration)) {
                $css .= (string)$node->css_class;
            }
            return $css;
        }
    }
}