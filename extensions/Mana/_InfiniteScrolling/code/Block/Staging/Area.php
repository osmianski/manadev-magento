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
class Mana_InfiniteScrolling_Block_Staging_Area extends Mage_Core_Block_Template {
    protected function _beforeToHtml() {
        $this->setTemplate("mana/infinitescrolling/staging/{$this->getMode()}.phtml");
        return parent::_beforeToHtml();
    }
    public function getMode() {
        $listBlock = $this->getParentBlock();
        /* @var $listBlock Mage_Catalog_Block_Product_List */
        return $listBlock->getMode();
    }
}