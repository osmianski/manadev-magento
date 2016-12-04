<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Block_Email_Order_Item extends Mage_Downloadable_Block_Sales_Order_Email_Items_Order_Downloadable
{
    public function getPurchasedLinkUrl($item) {
        return $this->getUrl('actions/domain/link', array(
            'id'        => $item->getLinkHash(),
            '_store'    => $this->getOrder()->getStore(),
            '_secure'   => true,
            '_nosid'    => true
        ));
    }

}