<?php
/**
 * @category    Mana
 * @package     ManaPro_SuperProductName
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * Generic helper functions for ManaPro_SuperProductName module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class ManaPro_SuperProductName_Helper_Data extends Mage_Core_Helper_Abstract {
    public function formatProductName($product = null) {
        if ($product) {
            return str_replace('{{name}}', $product->getName(), Mage::getStoreConfig('manapro_superproductname/general/template_format'));
        }
        else {
            return Mage::getStoreConfig('manapro_superproductname/general/template_format');
        }
    }
}