<?php
/**
 * @category    Mana
 * @package     Mana_Theme
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Theme_Block_Links extends Mage_Core_Block_Template
{
    public function addCompareLink()
    {
        /* @var $compare Mage_Catalog_Helper_Product_Compare */
        $compare = Mage::helper('catalog/product_compare');

        /* @var $theme Mana_Theme_Helper_Data */
        $theme = Mage::helper(strtolower('Mana_Theme'));

        /* @var $parentBlock Mage_Page_Block_Template_Links */
        $parentBlock = $this->getParentBlock();
        if ($parentBlock && $theme->getPageConfig('general/show_compare_link')) {
            $count = count($compare->getItemCollection()->load()->getItems());
            if (!$count) {
                $text = $this->__('Compare', $count);
            }
            elseif ($count == 1) {
                $text = $this->__('Compare (%s item)', $count);
            }
            else {
                $text = $this->__('Compare (%s items)', $count);
            }
            $url = $compare->getListUrl();
            $args = "'$url','compare','top:0,left:0,width=820,height=600,resizable=yes,scrollbars=yes'";
            $parentBlock->removeLinkByUrl($url);
            if ($count) {
                $parentBlock->addLink($text, $url, $text, false, array(), 45, null,
                    'onclick="popWin('.$args.'); return false;"');
            }
            else {
                $parentBlock->addLink($text, $url, $text, false, array(), 45, null,
                    'onclick="return false;"');
            }
        }
        return $this;
    }
}