<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterContent
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_FilterContent_Helper_Block_Search_Title extends ManaPro_FilterContent_Helper_Block {
    /**
     * @param Mage_CatalogSearch_Block_Result $block
     * @param string $key
     */
    public function before($block, $key) {
        if ($newTitle = $this->rendererHelper()->get('title')) {
            $block->setData('header_text', $newTitle);
        }
    }
}