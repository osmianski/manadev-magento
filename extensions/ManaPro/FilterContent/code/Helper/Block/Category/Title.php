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
class ManaPro_FilterContent_Helper_Block_Category_Title extends ManaPro_FilterContent_Helper_Block {
    /**
     * @param Mage_Catalog_Block_Category_View $block
     * @param string $key
     */
    public function before($block, $key) {
        $category = $block->getCurrentCategory();
        $oldTitle = $category->getName();
        if ($newTitle = $this->rendererHelper()->get('title')) {
            $block->setData($this->helper()->getOriginalContentKey($key), $oldTitle);
            $category->setData('name', $newTitle);
        }
    }

    /**
     * @param Mage_Catalog_Block_Category_View $block
     * @param string $key
     * @param Varien_Object $htmlObject
     */
    public function after($block, $key, $htmlObject) {
        if (($oldTitle = $block->getData($this->helper()->getOriginalContentKey($key))) !== null) {
            $category = $block->getCurrentCategory();
            $category->setData('name', $oldTitle);
            $block->unsetData($this->helper()->getOriginalContentKey($key));
        }
    }
}