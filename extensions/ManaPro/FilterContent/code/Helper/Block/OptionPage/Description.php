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
class ManaPro_FilterContent_Helper_Block_OptionPage_Description extends ManaPro_FilterContent_Helper_Block {
    /**
     * @param Mana_AttributePage_Block_Option_View $block
     * @param string $key
     */
    public function before($block, $key) {
        /* @var $optionPage Mana_AttributePage_Model_OptionPage_Store */
        $optionPage = Mage::registry('current_option_page');

        $oldDescription = $optionPage->getData('description');
        if ($newDescription = $this->rendererHelper()->get('description')) {
            $block->setData($this->helper()->getOriginalContentKey($key), $oldDescription);
            $optionPage->setData('description', $newDescription);
        }
    }

    /**
     * @param Mana_AttributePage_Block_Option_View $block
     * @param string $key
     * @param Varien_Object $htmlObject
     */
    public function after($block, $key, $htmlObject) {
        if (($oldDescription = $block->getData($this->helper()->getOriginalContentKey($key))) !== null) {
            /* @var $optionPage Mana_AttributePage_Model_OptionPage_Store */
            $optionPage = Mage::registry('current_option_page');

            $optionPage->setData('description', $oldDescription);
            $block->unsetData($this->helper()->getOriginalContentKey($key));
        }
    }
}