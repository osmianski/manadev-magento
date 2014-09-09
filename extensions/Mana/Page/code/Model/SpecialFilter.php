<?php
/** 
 * @category    Mana
 * @package     Mana_Page
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Page_Model_SpecialFilter extends Varien_Object implements Mana_Filters_Interface_Filter {
    public function init() {
    }

    /**
     * Returns whether this filter is applied
     *
     * @return bool
     */
    public function isApplied() {
        return $this->getData('is_applied');
    }

    /**
     * Applies filter values provided in URL to a given product collection
     *
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @return void
     */
    public function applyToCollection($collection) {
        $select = $collection->getSelect();
        $xml = new SimpleXMLElement($this->getData('condition'));
        $rule = $this->specialPageHelper()->rule($xml);
        $rule->join($select, $xml);
        $select->where($rule->where($xml));
        $sql = $select->__toString();
    }

    /**
     * Returns true if counting should be done on main collection query and false if a separated query should be done
     * Typically it should return false; however there are some cases (like not applied Solr facets) when it should
     * return true.
     *
     * @return bool
     */
    public function isCountedOnMainCollection() {
        return false;
    }

    /**
     * Applies counting query to the current collection. The result should be suitable to processCounts() method.
     * Typically, this method should return final result - option id/count pairs for option lists or
     * min/max pair for slider. However, in some cases (like not applied Solr facets) this method returns collection
     * object and later processCounts() extracts actual counts from this collections.
     *
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @return mixed
     */
    public function countOnCollection($collection) {
            $result = array();
            /* @var $resource Mana_Page_Resource_Special */
            $resource = Mage::getResourceSingleton('mana_page/special');

            $db = $collection->getConnection();

            $select = clone $collection->getSelect();
            $select->reset(Zend_Db_Select::COLUMNS);
            $select->reset(Zend_Db_Select::ORDER);
            $select->reset(Zend_Db_Select::GROUP);
            $select->reset(Zend_Db_Select::LIMIT_COUNT);
            $select->reset(Zend_Db_Select::LIMIT_OFFSET);
            $select->columns("COUNT(`e`.`entity_id`)");

            $xml = new SimpleXMLElement($this->getData('condition'));
            $rule = $this->specialPageHelper()->rule($xml);
            $rule->join($select, $xml);
            $select->where($rule->where($xml));

            return $db->fetchOne($select);
    }

    public function getRangeOnCollection($collection) {
        return array();
    }

    /**
     * Returns option id/count pairs for option lists or min/max pair for slider. Typically, this method just returns
     * $counts. However, in some cases (like not applied Solr facets) this method gets a collection object with Solr
     * results and extracts those results.
     *
     * @param mixed $counts
     * @return array
     */
    public function processCounts($counts) {
        return $counts;
    }

    /**
     * Returns whether a given filter $modelToBeApplied should be applied when this filter is being counted. Typically,
     * returns true for all filters except this one.
     *
     * @param $modelToBeApplied
     * @return mixed
     */
    public function isFilterAppliedWhenCounting($modelToBeApplied) {
        return true;
    }

    /**
     * Adds all selected items of this filters to the layered navigation state object
     *
     * @return void
     */
    public function addToState() {
        $this->filterHelper()->getLayer()->getState()->addFilter($this->_createItemEx(array(
            'label' => $this->getData('title'),
            'special' => true,
            'value' => $this->getData('url_key'),
            'm_selected' => true,
            'm_show_selected' => false,
        )));
    }

    protected function _createItemEx($data) {
        $filterOptions = $this->getData('host_filter');
        $filter = new Varien_Object();
        $filter
            ->setData('name', $filterOptions->getName())
            ->setData('request_var', $filterOptions->getCode())
            ->setData('filter_options', $filterOptions);

        return Mage::getModel('mana_filters/item')
            ->setData($data)
            ->setFilter($filter);
    }

    public function addToHostFilterOptions(&$result, $code) {
        $filterOptions = $this->getData('host_filter');
        /* @var $query Mana_Filters_Model_Query */
        $query = $this->getData('query');
        if ($filterOptions->getCode() != $code) {
            return;
        }

        $result[] = array(
            'label' => $this->getData('title'),
            'special' => true,
            'value' => $this->getData('url_key'),
            'count' => $query->getFilterCounts($this->getData('special_filter_code')),
            'position' => $this->getData('position'),
            'm_selected' => $this->isApplied(),
            'm_show_selected' => $this->isApplied(),
        );

    }


    #region Dependencies
    /**
     * @return Mana_Page_Helper_Special
     */
    public function specialPageHelper() {
        return Mage::helper('mana_page/special');
    }

    /**
     * @return Mana_Filters_Helper_Data
     */
    public function filterHelper() {
        return Mage::helper('mana_filters');
    }

    #endregion
}