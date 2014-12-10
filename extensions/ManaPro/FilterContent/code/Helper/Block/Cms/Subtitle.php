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
class ManaPro_FilterContent_Helper_Block_Cms_Subtitle extends ManaPro_FilterContent_Helper_Block {
    /**
     * @param Mage_Core_Block_Template $block
     * @param string $key
     * @param Varien_Object $htmlObject
     */
    public function after($block, $key, $htmlObject) {
        if ($subtitle = $this->rendererHelper()->get('subtitle')) {
            $html = $htmlObject->getData('html');
            $insertAfter = '</h1>';
            if (($pos = strpos($html, $insertAfter)) !== false) {
                $insertPos = $pos + strlen($insertAfter);
                $htmlObject->setData('html', substr($html, 0, $insertPos) . $subtitle . substr($html, $insertPos));
            }
        }
    }
}