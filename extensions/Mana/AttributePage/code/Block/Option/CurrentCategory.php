<?php

class Mana_AttributePage_Block_Option_CurrentCategory extends Mana_AttributePage_Block_Option_Images
{
    /**
     * @return Mana_AttributePage_Resource_OptionPage_Store_Collection
     */
    public function getCollection() {
        /* @var Mana_AttributePage_Resource_OptionPage_Store_Collection $collection */
        $collection = $this->createOptionPageCollection()
            ->addStoreFilter(Mage::app()->getStore()->getId())
            ->setOrder('position', 'ASC');

        return $this->addCategoryFilterToCollection($collection);
    }

    /**
     * @param Mana_AttributePage_Resource_OptionPage_Store_Collection $collection
     * @return Mana_AttributePage_Resource_OptionPage_Store_Collection
     */
    protected function addCategoryFilterToCollection($collection) {
        $products = $this->getLayer()->getCurrentCategory()->getProductCollection();
        $this->getLayer()->prepareProductCollection($products);

        $db = $products->getConnection();
        /* @var $res Mage_Core_Model_Resource */
        $res = Mage::getSingleton('core/resource');

        $select = $products->getSelect()
            ->reset(Varien_Db_Select::COLUMNS)
            ->distinct()
            ->joinInner(array('eav' => $res->getTableName('catalog_product_index_eav')),
                "`eav`.`entity_id` = `e`.`entity_id` AND " .
                //$db->quoteInto("`eav`.`attribute_id` = ?", $attributeId) . " AND " .
                $db->quoteInto("`eav`.`store_id` = ?", Mage::app()->getStore()->getId()), null)
            ->joinInner(array('option_page' => $res->getTableName('m_option_page_global')),
                "`option_page`.`option_id_0` = `eav`.`value`", 'id');

        $optionPageGlobalIds = $db->fetchCol($select);
        $collection->getSelect()->where("`main_table`.`option_page_global_id` IN (?)", $optionPageGlobalIds);
        return $collection;
    }

    /**
     * @return Mage_Catalog_Model_Layer|Mage_Core_Model_Abstract
     */
    protected function getLayer() {
        return Mage::getSingleton('catalog/layer');
    }

}