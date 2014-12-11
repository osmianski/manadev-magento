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
class ManaPro_FilterContent_Helper_Block_Cms_Description extends ManaPro_FilterContent_Helper_Block {
    /**
     * @param Mage_Cms_Block_Page $block
     * @param string $key
     */
    public function before($block, $key) {
        $page = Mage::getSingleton('cms/page');
        $oldDescription = $page->getData('content');
        if ($newDescription = $this->rendererHelper()->get('description')) {
            $block->setData($this->helper()->getOriginalContentKey($key), $oldDescription);
            $page->setData('content', $newDescription);
        }
    }

    /**
     * @param Mage_Cms_Block_Page $block
     * @param string $key
     * @param Varien_Object $htmlObject
     */
    public function after($block, $key, $htmlObject) {
        if (($oldDescription = $block->getData($this->helper()->getOriginalContentKey($key))) !== null) {
            $page = Mage::getSingleton('cms/page');
            $page->setData('content', $oldDescription);
            $block->unsetData($this->helper()->getOriginalContentKey($key));
        }
    }
}