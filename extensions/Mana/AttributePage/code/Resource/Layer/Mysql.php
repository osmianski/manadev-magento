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
class Mana_AttributePage_Resource_Layer_Mysql extends Mage_Core_Model_Mysql4_Abstract  {
    /**
     * Resource initialization
     */
    protected function _construct() {
        $this->_init('catalog/product_index_eav', 'entity_id');
    }

    public function apply() {
        $layer = Mage::getSingleton('catalog/layer');
        $collection = $layer->getProductCollection();
        $select = $collection->getSelect();

        $connection = $this->_getReadAdapter();

        /* @var $attributePage Mana_AttributePage_Model_Page */
        $attributePage = Mage::registry('current_attribute_page');

        /* @var $optionPage Mana_AttributePage_Model_Option_Page */
        $optionPage = Mage::registry('current_option_page');

        // fix select to work even if category is not root
        $from = $select->getPart(Varien_Db_Select::FROM);
        if (isset($from['cat_index'])) {
            $from['cat_index']['joinCondition'] = preg_replace("/AND `?cat_index`?\\.`?is_parent`?='?1'?/", '', $from['cat_index']['joinCondition']);
            $select->setPart(Varien_Db_Select::FROM, $from);
        }

        // apply option page filters
        for ($i = 0; $i < Mana_AttributePage_Model_Page::MAX_ATTRIBUTE_COUNT; $i++) {
            if (($attributeId = $attributePage->getData("attribute_id_$i")) && ($optionId = $optionPage->getData("option_id_$i"))) {
                $tableAlias = "mapidx_$attributeId";
                $conditions = array(
                    "{$tableAlias}.entity_id = e.entity_id",
                    $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attributeId),
                    $connection->quoteInto("{$tableAlias}.store_id = ?", $collection->getStoreId()),
                    $connection->quoteInto("{$tableAlias}.value = ?", $optionId),
                );
                $conditions = join(' AND ', $conditions);
                $select
                    ->distinct()
                    ->join(array($tableAlias => $this->getMainTable()), $conditions, null);

                return $this;

            }
        }
    }
}