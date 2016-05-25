<?php
/** 
 * @category    Mana
 * @package     Mana_NegativePrices
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_NegativePrices_Rewrite_PriceFormHelper extends Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Price {
    public function __construct($attributes = array()) {
        parent::__construct($attributes);
        $this->removeClass('validate-zero-or-greater');
    }
}