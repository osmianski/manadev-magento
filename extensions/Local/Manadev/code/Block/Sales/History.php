<?php

class Local_Manadev_Block_Sales_History extends Mage_Sales_Block_Order_History {
    protected function _prepareLayout() {
        $this->getOrders()->load();
        return $this;
    }
}