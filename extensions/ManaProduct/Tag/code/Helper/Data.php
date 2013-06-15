<?php
/**
 * @category    Mana
 * @package     ManaProduct_Tag
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * Generic helper functions for ManaProduct_Tag module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class ManaProduct_Tag_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function beforeImage($targetBlock, $product)
    {
        /* @var $layout Mage_Core_Model_Layout */
        $layout = Mage::getSingleton('core/layout');

        if ($block = $layout->getBlock('m_product_before_image')) {
            /* @var $block ManaProduct_Tag_Block_Before */
            return $block
                ->setTargetBlock($targetBlock)
                ->setProduct($product)
                ->toHtml();
        }
        else {
            return '';
        }
    }
    public function afterImage($targetBlock, $product)
    {
        /* @var $layout Mage_Core_Model_Layout */
        $layout = Mage::getSingleton('core/layout');

        if ($block = $layout->getBlock('m_product_after_image')) {
            /* @var $block ManaProduct_Tag_Block_After */
            return $block
                ->setTargetBlock($targetBlock)
                ->setProduct($product)
                ->toHtml();
        }
        else {
            return '';
        }
    }
}