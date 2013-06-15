<?php
/**
 * @category    Mana
 * @package     Mana_Social
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Social_Block_Facebook_Like_Product extends Mana_Social_Block_Facebook_Like
{
    /**
     * @var Mage_Catalog_Model_Product
     */
    protected $_product;

    /**
     * @param Mage_Catalog_Model_Product $value
     * @return Mana_Social_Block_Facebook_Like_Product
     */
    public function setProduct($value) {
        $this->_product = $product = $value;
        $this->setPageUrl($product->getProductUrl());
        $this->setWidth(90);
        $this->setShowSend(false);
        $this->setFbLayout('button_count');
        return $this;
    }
}