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

    public function setProduct($value) {
        $this->_product = $value;
        return $this;
    }
}