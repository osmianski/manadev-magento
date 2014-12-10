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
class ManaPro_FilterContent_Helper_Block_Cms_Title extends ManaPro_FilterContent_Helper_Block {
    /**
     * @param Mage_Core_Block_Template $block
     * @param string $key
     */
    public function before($block, $key) {
        $oldTitle = $block->getData('content_heading');
        if ($newTitle = $this->rendererHelper()->get('title')) {
            $block->setData($this->helper()->getOriginalContentKey($key), $oldTitle);
            $block->setData('content_heading', $newTitle);
        }
    }

    /**
     * @param Mage_Core_Block_Template $block
     * @param string $key
     * @param Varien_Object $htmlObject
     */
    public function after($block, $key, $htmlObject) {
        if (($oldTitle = $block->getData($this->helper()->getOriginalContentKey($key))) !== null) {
            $block->setData('content_heading', $oldTitle);
            $block->unsetData($this->helper()->getOriginalContentKey($key));
        }
    }
}