<?php
/** 
 * @category    Mana
 * @package     Mana_Content
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Content_Model_Generator_Tree extends Mana_Menu_Model_Generator {

    /**
     * @param Mage_Core_Model_Config_Element $element
     */
    public function extend($element) {
        $collection = $this->_getCollection();

        $collection->setParentFilter(null);
        $filteredIds = false;
        if ($filter = Mage::registry('filter')) {
            $filterCollection = $this->_getCollection();
            $searchFilteredIds = $filterCollection->filterTreeByTitle($filter['search']);
            $relatedProductsFilteredIds = $filterCollection->filterTreeByRelatedProducts($filter['related_products']);
            $tagsFilteredIds = $filterCollection->filterTreeByTags($filter['tags']);
            $filteredIds = $searchFilteredIds;
            if(!empty($relatedProductsFilteredIds)) {
                $filteredIds = array_intersect($filteredIds, $relatedProductsFilteredIds);
            }
            if(!empty($tagsFilteredIds)) {
                $filteredIds = array_intersect($filteredIds, $tagsFilteredIds);
            }
        }
        $collection->addOrder('position', Varien_Data_Collection_Db::SORT_ORDER_ASC);

        foreach($collection as $record) {
            $this->_extendRecursively($element,$record, $filteredIds);
        }
    }

    /**
     * @param $element
     * @param $book Mana_Content_Model_Page_Hierarchical
     */
    protected function _extendRecursively($element, $book, $filteredIds) {
        $id = $book->getId();
        $xmlId = 'c_' . $id;
        $route = "mana_content/book/view";
        $params = array('_use_rewrite' => true, 'id' => $id, '_nosid' => true);
        if ($filter = Mage::registry('filter')) {
            $query = array();
            if(!is_null($filter['search'])) {
                $query['search'] = $filter['search'];
            }
            if(!empty($filter['related_products'])) {
                $query['related_products'] = implode(",", $filter['related_products']);
            }
            $params['_query'] = $query;
        }
        $url = Mage::getUrl($route, $params);

        if(!is_array($filteredIds) || in_array($id, $filteredIds)) {
            $element->items->$xmlId->url = $url;
            $element->items->$xmlId->route = $route;
            $element->items->$xmlId->label = $book->getTitle();
            $element->items->$xmlId->selected = Mage::registry('current_book_page')->getId() == $id;
            $book->loadChildPages();

            foreach($book->getChildPages() as $record) {
                $this->_extendRecursively($element->items->$xmlId, $record, $filteredIds);
            }
        }
    }

    private function _getCollection() {
        $collection = Mage::getResourceModel("mana_content/page_store_collection");
        $collection->addFieldToFilter('store_id', Mage::app()->getStore()->getId());

        return $collection;
    }

    #region Dependencies
    /**
     * @return Mana_Admin_Helper_Data
     */
    public function adminHelper() {
        return Mage::helper('mana_admin');
    }

    /**
     * @return Mana_Core_Helper_Data
     */
    public function coreHelper() {
        return Mage::helper('mana_core');
    }

    /**
     * @return Mana_Db_Helper_Data
     */
    public function dbHelper() {
        return Mage::helper('mana_db');
    }

    /**
     * @return Mana_Core_Helper_Json
     */
    public function jsonHelper() {
        return Mage::helper('mana_core/json');
    }

    #endregion
}