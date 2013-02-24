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
class Mana_Social_Block_Share_Product extends Mana_Social_Block_Share
{
    /**
     * @param Mage_Core_Block_Abstract $action
     * @return Mana_Social_Block_Share
     */
    protected function _initSharingAction($action) {
        if (method_exists($action, 'setProduct')) {
            $action->setProduct($this->getProduct());
            return true;
        }
        else {
            return false;
        }
    }

    protected function _getBlockSuffix() {
        return '_product';
    }

    /**
     * Retrieve currently viewed product object
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        if (!$this->hasData('product')) {
            $this->setData('product', Mage::registry('product'));
        }

        return $this->getData('product');
    }
}