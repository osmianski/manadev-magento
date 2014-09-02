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
            if (isset($counts[$id])) {
                $isSelected = $this->isApplied($special['url_key']);
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
            $values = explode('_', Mage::app()->getRequest()->getParam($this->getRequestVar()));

            $this->_applied = $values ? array_filter($values) : array();
        }
        return $this->_applied;
    }

    /**
     * @param Mana_Filters_Model_Item $item
     * @return string
     */
    public function getUrl($item) {
        $values = $this->getAppliedOptions();
        if (!$this->isApplied($item->getData('value'))) {
            $values[] = $item->getData('value');
        }

    	$query = array(
            $this->getRequestVar() => implode('_', $values),
            Mage::getBlockSingleton('page/html_pager')->getPageVarName() => null // exclude current page from urls
        );

        $params = array('_current'=>true, '_m_escape' => '', '_use_rewrite'=>true, '_query'=>$query, '_secure' => Mage::app()->getFrontController()->getRequest()->isSecure());
        return Mage::helper('mana_filters')->markLayeredNavigationUrl(Mage::getUrl('*/*/*', $params), '*/*/*', $params);
    }

    /**
     * @param Mana_Filters_Model_Item $item
     * @return string
     */
    public function getReplaceUrl($item) {
        $values = $this->getAppliedOptions();
        if (!$this->isApplied($item->getData('value'))) {
            $values[] = $item->getData('value');
        }

    	$query = array(
            $this->getRequestVar() => implode('_', $values),
            Mage::getBlockSingleton('page/html_pager')->getPageVarName() => null // exclude current page from urls
        );

        $params = array('_current'=>true, '_m_escape' => '', '_use_rewrite'=>true, '_query'=>$query, '_secure' => Mage::app()->getFrontController()->getRequest()->isSecure());
        return Mage::helper('mana_filters')->markLayeredNavigationUrl(Mage::getUrl('*/*/*', $params), '*/*/*', $params);
    }

    /**
     * @param Mana_Filters_Model_Item $item
     * @return string
     */
    public function getRemoveUrl($item) {
        $values = $this->getAppliedOptions();
        if (!$this->isApplied($item->getData('value'))) {
            $values[] = $item->getData('value');
        }

    	$query = array(
            $this->getRequestVar() => implode('_', $values),
            Mage::getBlockSingleton('page/html_pager')->getPageVarName() => null // exclude current page from urls
        );

        $params = array('_current'=>true, '_m_escape' => '', '_use_rewrite'=>true, '_query'=>$query, '_secure' => Mage::app()->getFrontController()->getRequest()->isSecure());
        return Mage::helper('mana_filters')->markLayeredNavigationUrl(Mage::getUrl('*/*/*', $params), '*/*/*', $params);
    }

    public function isApplied($urlKey) {
        return in_array($urlKey, $this->getAppliedOptions());
    }

    #region Dependencies

    /**
     * @return Mana_Core_Helper_Data
     */
    public function coreHelper() {
        return Mage::helper('mana_core');
    }

    #endregion
}