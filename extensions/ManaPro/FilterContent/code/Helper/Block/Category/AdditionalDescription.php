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
class ManaPro_FilterContent_Helper_Block_Category_AdditionalDescription extends ManaPro_FilterContent_Helper_Block {
    /**
     * @param Mage_Catalog_Block_Category_View $block
     * @param string $key
     */
    public function before($block, $key) {
        $category = $block->getCurrentCategory();
        $oldDescription = $category->getData('description');
        if ($newDescription = $this->rendererHelper()->get('additional_description')) {
            $block->setData($this->helper()->getOriginalContentKey($key), $oldDescription);
            $category->setData('description', $oldDescription . $newDescription);
        }
    }

    /**
     * @param Mage_Catalog_Block_Category_View $block
     * @param string $key
     * @param Varien_Object $htmlObject
     */
    public function after($block, $key, $htmlObject) {
        if (($oldDescription = $block->getData($this->helper()->getOriginalContentKey($key))) !== null) {
            $category = $block->getCurrentCategory();
            $category->setData('description', $oldDescription);
            $block->unsetData($this->helper()->getOriginalContentKey($key));
        }
    }
}