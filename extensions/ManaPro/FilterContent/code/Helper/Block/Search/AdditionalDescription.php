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
class ManaPro_FilterContent_Helper_Block_Search_AdditionalDescription extends ManaPro_FilterContent_Helper_Block {
    /**
     * @param Mage_Catalog_Block_Product_List $block
     * @param string $key
     * @param Varien_Object $htmlObject
     */
    public function after($block, $key, $htmlObject) {
        if ($newDescription = $this->rendererHelper()->get('additional_description')) {
            $htmlObject->setData('html', $newDescription . $htmlObject->getData('html'));
        }
    }
}