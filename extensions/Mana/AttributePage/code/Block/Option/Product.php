<?php
/** 
 * @category    Mana
 * @package     Mana_AttributePage
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_AttributePage_Block_Option_Product extends Mage_Core_Block_Template {
    /**
     * @var Mana_AttributePage_Model_AttributePage_Store
     */
    protected $_collection;

    /**
     * @return Mana_AttributePage_Model_AttributePage_Store
     */
    public function getOptionPages()
    {
        if (!$this->_collection) {
            $collection = $this->createOptionPageCollection();
            $collection->addProductFilter(Mage::app()->getStore()->getId(), $this->getProduct()->getId());
            $this->_collection = $collection;
        }
        return $this->_collection;
    }

    #region Dependencies

    /**
     * @return Mana_Core_Helper_Files
     */
    public function filesHelper()
    {
        return Mage::helper('mana_core/files');
    }

    /**
     * @return Mana_AttributePage_Resource_OptionPage_Store_Collection
     */
    public function createOptionPageCollection() {
        return Mage::getResourceModel('mana_attributepage/optionPage_store_collection');
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

    #endregion
}