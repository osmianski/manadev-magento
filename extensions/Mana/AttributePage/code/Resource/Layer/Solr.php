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
class Mana_AttributePage_Resource_Layer_Solr extends Mage_Core_Model_Mysql4_Abstract  {
    /**
     * Resource initialization
     */
    protected function _construct() {
        $this->_init('catalog/product_index_eav', 'entity_id');
    }

    public function apply() {
        /* @var $layer Enterprise_Search_Model_Catalog_Layer */
        $layer = Mage::helper('mana_core/layer')->getLayer();

        $collection = $layer->getProductCollection();
        $select = $collection->getSelect();

        $connection = $this->_getReadAdapter();

        /* @var $attributePage Mana_AttributePage_Model_AttributePage_Store */
        $attributePage = Mage::registry('current_attribute_page');

        /* @var $optionPage Mana_AttributePage_Model_OptionPage_Store */
        $optionPage = Mage::registry('current_option_page');

        // fix select to work even if category is not root
        $from = $select->getPart(Varien_Db_Select::FROM);
        if (isset($from['cat_index'])) {
            $from['cat_index']['joinCondition'] = preg_replace("/AND `?cat_index`?\\.`?is_parent`?='?1'?/", '', $from['cat_index']['joinCondition']);
            $select->setPart(Varien_Db_Select::FROM, $from);
        }

        // apply option page filters
        $select->distinct();
        for ($i = 0; $i < Mana_AttributePage_Model_AttributePage_Store::MAX_ATTRIBUTE_COUNT; $i++) {
            if (($attributeId = $attributePage->getData("attribute_id_$i")) && ($optionId = $optionPage->getData("option_id_$i"))) {
                /* @var $eavConfig mage_Eav_Model_Config */
                $eavConfig = Mage::getSingleton('eav/config');
                $entityType = $eavConfig->getEntityType('catalog_product');
                $attribute = $eavConfig->getAttribute($entityType, $attributeId);

                $collection->addFqFilter(array($this->getFilterField($attribute) => $optionId));
            }
            else {
                break;
            }
        }

        return $this;
    }

    public function getFilterField($model) {
        /* @var $engine Enterprise_Search_Model_Resource_Engine */
        $engine = Mage::getResourceSingleton('enterprise_search/engine');
        if (method_exists($engine, 'getSearchEngineFieldName')) {
            return $engine->getSearchEngineFieldName($model, 'nav');
        }
        else {
            /* @var $helper Enterprise_Search_Helper_Data */
            $helper = Mage::helper('enterprise_search');
            return $helper->getAttributeSolrFieldName($model);
        }
    }
}