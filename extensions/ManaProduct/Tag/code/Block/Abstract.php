<?php
/**
 * @category    Mana
 * @package     ManaProduct_Tag
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 * @method Mage_Catalog_Block_Product_Abstract getTargetBlock()
 * @method ManaProduct_Tag_Block_Abstract setTargetBlock(Mage_Catalog_Block_Product_Abstract $value)
 * @method Mage_Catalog_Model_Product getProduct()
 * @method ManaProduct_Tag_Block_Abstract setProduct(Mage_Catalog_Model_Product $value)
 */
class ManaProduct_Tag_Block_Abstract extends Mage_Core_Block_Template
{
    public function getTagClasses() {
        $result = array();

        if ($this->getProduct()->getPrice() != $this->getProduct()->getFinalPrice()) {
            $result[] = 'm-tag m-sale';
        }

        return $result;
    }
}