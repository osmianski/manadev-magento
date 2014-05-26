<?php
/** 
 * @category    Mana
 * @package     Mana_InfiniteScrolling
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_InfiniteScrolling_Block_Engine extends Mage_Core_Block_Text_List {
    protected function _prepareLayout()
    {
        /* @var $layoutHelper Mana_Core_Helper_Layout */
        $layoutHelper = Mage::helper('mana_core/layout');
        $layoutHelper->delayPrepareLayout($this);

        return $this;
    }

    public function delayedPrepareLayout() {
        $this->_prepareClientSideBlock();
    }

    protected function _prepareClientSideBlock() {
        $this->setData('m_client_side_block', array(
            'type' => 'Mana/InfiniteScrolling/Engine',
            'list' => $this->getData('list'),
            'list_item' => $this->getData('list_item'),
        ));

        return $this;
    }
}