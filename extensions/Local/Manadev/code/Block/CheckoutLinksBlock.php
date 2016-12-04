<?php

/**
 * Created by PhpStorm.
 * User: Mn
 * Date: 2016-11-25
 * Time: 15:04
 */
class Local_Manadev_Block_CheckoutLinksBlock extends Mage_Checkout_Block_Links
{
    public function addCartLink()
    {
        $parentBlock = $this->getParentBlock();
        if ($parentBlock && Mage::helper('core')->isModuleOutputEnabled('Mage_Checkout')) {
            $count = $this->getSummaryQty() ? $this->getSummaryQty()
                : $this->helper('checkout/cart')->getSummaryCount();
            if ($count == 1) {
                $title = $this->__('My Cart (%s item)', $count);
                $label = $this->__('My Cart (%s)', $count);
            } elseif ($count > 0) {
                $title = $this->__('My Cart (%s items)', $count);
                $label = $this->__('My Cart (%s)', $count);
            } else {
                $title = $this->__('My Cart');
                $label = $this->__('My Cart');
            }

            $parentBlock->removeLinkByUrl($this->getUrl('checkout/cart'));
            $parentBlock->addLink($label, 'checkout/cart', $title, true, array(), 50, null, 'class="top-link-cart"');
        }
        return $this;
    }
}