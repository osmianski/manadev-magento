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
    protected $_sizeBasedClassXmlLoaded;
    protected $_sizeBasedClassXml;

    public function beforeImage($targetBlock, $product, $width = false, $height = false)
    {
        /* @var $layout Mage_Core_Model_Layout */
        $layout = Mage::getSingleton('core/layout');

        /* @var $block ManaProduct_Tag_Block_Before */
        $block = $layout->getBlockSingleton('manaproduct_tag/before');
        return $block
            ->setTargetBlock($targetBlock)
            ->setProduct($product)
            ->setWidth($width)
            ->setHeight($height)
            ->toHtml();
    }
    public function afterImage($targetBlock, $product, $width = false, $height = false)
    {
        /* @var $layout Mage_Core_Model_Layout */
        $layout = Mage::getSingleton('core/layout');

        /* @var $block ManaProduct_Tag_Block_Before */
        $block = $layout->getBlockSingleton('manaproduct_tag/after');
        return $block
            ->setTargetBlock($targetBlock)
            ->setProduct($product)
            ->setWidth($width)
            ->setHeight($height)
            ->toHtml();
    }

    public function getSizeBasedClassXml() {
        if (!$this->_sizeBasedClassXmlLoaded) {
            if ($xml = Mage::getStoreConfig('manaproduct_tag/design/size_based_css_class')) {
                $this->_sizeBasedClassXml = simplexml_load_string("<config>$xml</config>");
            }
            else {
                $this->_sizeBasedClassXml = null;
            }
            $this->_sizeBasedClassXmlLoaded = true;
        }
        return $this->_sizeBasedClassXml;

    }
}