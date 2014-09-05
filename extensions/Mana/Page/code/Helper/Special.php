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
class Mana_Page_Helper_Special extends Mage_Core_Helper_Abstract  {
    protected $_counts = array();
    protected $_applied;

    public function getOptionData($code, $counts) {
        /* @var $resource Mana_Page_Resource_Special */
        $resource = Mage::getResourceSingleton('mana_page/special');

        $result = array();

        foreach ($resource->getData(Mage::app()->getStore()->getId()) as $id => $special) {
            if (isset($counts[$id]) && $this->getFilterCodeById($special['filter']) == $code) {
                $isSelected = $this->isApplied($code, $special['url_key']);
                $result[] = array(
                    'label' => $special['title'],
                    'special' => true,
                    'value' => $special['url_key'],
                    'count' => $counts[$id],
                    'm_selected' => $isSelected,
                    'm_show_selected' => $isSelected,
                );

            }
        }

        return $result;
    }

    /**
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @return array
     */
    public function countOnCollection($collection) {
        $key = spl_object_hash($collection);

        if (!isset($this->_counts[$key])) {
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

            foreach ($resource->getData(Mage::app()->getStore()->getId()) as $id => $special) {
                if ($special['filter']) {
                    $xml = new SimpleXMLElement($special['condition']);
                    $rule = $this->rule($xml);
                    $rule->join($select, $xml);
                    $select->where($rule->where($xml));

                    $result[$id] = $db->fetchOne($select);
                }
            }

            $this->_counts[$key] = $result;
        }

        return $this->_counts[$key];
    }

    public function applyToCollection($collection) {

    }

    /**
     * @param SimpleXmlElement $xml
     * @return Mana_Page_Helper_Special_Rule
     */
    public function rule($xml) {
        return Mage::helper((string)Mage::getConfig()->getNode('mana_page/special/' . $xml->getName()));
    }

    public function getRequestVar() {
        return 'special-options';
    }

    public function getAppliedOptions() {
        if (!$this->_applied) {
            /* @var $resource Mana_Page_Resource_Special */
            $resource = Mage::getResourceSingleton('mana_page/special');

            $result = array();

            foreach ($resource->getData(Mage::app()->getStore()->getId()) as $id => $special) {
                if ($special['filter']) {
                    $code = $this->getFilterCodeById($special['filter']);
                    if ($param = Mage::app()->getRequest()->getParam($code)) {
                        $values = explode('_', $param);
                        if (in_array($special['url_key'], $values)) {
                            if (isset($result[$code])) {
                                $result[$code] = array();
                            }
                            $result[$code][] = $special['url_key'];
                        }
                    }

                }
            }

            $this->_applied = $result;
        }
        return $this->_applied;
    }

    public function isApplied($code, $urlKey) {
        foreach ($this->getAppliedOptions() as $appliedCode => $options) {
            if ($code == $appliedCode && in_array($urlKey, $options)) {
                return true;
            }
        }
        return false;
    }

    public function getFilterCodeById($id) {
        $collection = $this->layerHelper()->getFilterOptionsCollection();
        if ($filter = $this->coreHelper()->collectionFind($collection, 'global_id', $id)) {
            return $filter->getData('code');
        }
        else {
            return false;
        }
    }

    public function isUrlKey($code, $urlKey) {
        /* @var $resource Mana_Page_Resource_Special */
        $resource = Mage::getResourceSingleton('mana_page/special');

        foreach ($resource->getData(Mage::app()->getStore()->getId()) as $special) {
            if ($special['filter'] && $this->getFilterCodeById($special['filter']) == $code
                && $special['url_key'] == $urlKey)
            {
                return true;
            }
        }

        return false;
    }


    #region Dependencies

    /**
     * @return Mana_Core_Helper_Data
     */
    public function coreHelper() {
        return Mage::helper('mana_core');
    }

    /**
     * @return Mana_Filters_Helper_Data
     */
    public function layerHelper() {
        return Mage::helper('mana_filters');
    }

    #endregion
}