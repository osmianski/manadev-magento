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
class ManaPro_FilterContent_Helper_Block_OptionPage_Title extends ManaPro_FilterContent_Helper_Block {
    /**
     * @param Mana_AttributePage_Block_Option_View $block
     * @param string $key
     */
    public function before($block, $key) {
        /* @var $optionPage Mana_AttributePage_Model_OptionPage_Store */
        $optionPage = Mage::registry('current_option_page');

        $oldTitle = $optionPage->getData('heading');
        if ($newTitle = $this->rendererHelper()->get('title')) {
            $block->setData($this->helper()->getOriginalContentKey($key), $oldTitle);
            $optionPage->setData('heading', $newTitle);
        }
    }

    /**
     * @param Mage_Catalog_Block_Category_View $block
     * @param string $key
     * @param Varien_Object $htmlObject
     */
    public function after($block, $key, $htmlObject) {
        if (($oldTitle = $block->getData($this->helper()->getOriginalContentKey($key))) !== null) {
            /* @var $optionPage Mana_AttributePage_Model_OptionPage_Store */
            $optionPage = Mage::registry('current_option_page');

            $optionPage->setData('heading', $oldTitle);
            $block->unsetData($this->helper()->getOriginalContentKey($key));
        }
    }
}