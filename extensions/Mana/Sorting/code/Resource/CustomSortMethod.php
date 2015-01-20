<?php
/** 
 * @category    Mana
 * @package     Mana_Sorting
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Sorting_Resource_CustomSortMethod extends Mage_Core_Model_Mysql4_Abstract implements Mana_Sorting_ResourceInterface {
    /** @var Mana_Sorting_Model_Method_Store $sortMethodModel */
    protected $sortMethodModel;

    public function setCustomSortMethodId($id) {
        $this->sortMethodModel->load($id, 'method_id');
    }

    /**
     * Resource initialization
     */
    protected function _construct() {
        $store_id = Mage::app()->getStore()->getId();
        $this->sortMethodModel = Mage::getModel('mana_sorting/method_store');
        $this->sortMethodModel->setData('store_id', $store_id);
        $this->_setResource('catalog');
    }

    /**
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @param string $order
     * @param string $direction
     */
    public function setOrder($collection, $order, $direction) {
        if(!$this->sortMethodModel->getId()) {
            throw new Exception('Custom sort model is not set.');
        }

        $select = $collection->getSelect();
        if (Mage::helper('mana_sorting')->getOutOfStockOption()) {
            $select
                    ->joinLeft(
                        array('s' => $this->getTable('cataloginventory/stock_item')),
                        ' s.product_id = e.entity_id ',
                        array()
                    );
            $select->order("s.is_in_stock desc");
        }
        for($x=0;$x<=4;$x++) {
            $attribute_id = $this->sortMethodModel->getData('attribute_id_'.$x);
            $sorting_method = $this->sortMethodModel->getData('sorting_method_' . $x);
            $directionAttribute = $this->sortMethodModel->getData("attribute_id_{$x}_sortdir") == 1 ? 'asc' : 'desc';
            if(is_numeric($attribute_id)) {
                $_attribute_code = Mage::getModel('eav/entity_attribute')->load($attribute_id)->getAttributeCode();
                $collection->addAttributeToSort($_attribute_code, $directionAttribute);
            } elseif($sorting_method != "") {
                $xmls = $this->sortingHelper()->getSortingMethodXmls();
                $resource = Mage::getResourceSingleton((string)$xmls[$sorting_method]->resource);
                $resource->setOrder($collection, $order, $directionAttribute);
                break;
            }
        }
    }

    #region Dependencies

    /**
     * @return Mana_Sorting_Helper_Data
     */
    public function sortingHelper() {
        return Mage::helper('mana_sorting');
    }

    #endregion
}