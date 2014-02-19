<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterShowMore
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/* BASED ON SNIPPET: New Module/Helper/Data.php */
/**
 * Generic helper functions for ManaPro_FilterShowMore module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class ManaPro_FilterShowMore_Helper_Data extends Mage_Core_Helper_Abstract {
	public function getShowAllSuffix() {
        /* @var $_core Mana_Core_Helper_Data */ $_core = Mage::helper(strtolower('Mana_Core'));
		return $_core->getStoreConfig('mana_filters/seo/show_all_suffix');
	}
	/**
	 * Returns true if all the items show be shown for that filter (as specified in URL) and false if item list
	 * should be truncated
	 * @param Mana_Filters_Model_Filter_Attribute $filter
	 * @return boolean
	 */
	public function isShowAllRequested($filter) {
//	    if ($filter->getFilterOptions()->getDisplay() == 'colors') {
//	        return true;
//	    }
    	$value = Mage::app()->getRequest()->getParam($filter->getRequestVar().$this->getShowAllSuffix());
		return $value && $value == 1 ? true : false;    
	}
	/**
	 * Returns current URL modified to enable showing full item list 
	 * @param Mana_Filters_Model_Filter_Attribute $filter
	 * @return string
	 */
	public function getShowMoreUrl($filter) {
    	/* @var $ext Mana_Filters_Helper_Extended */ $ext = Mage::helper(strtolower('Mana_Filters/Extended'));
        $params = array('_secure' => Mage::app()->getFrontController()->getRequest()->isSecure());
		$params['_current']     = true;
        $params['_use_rewrite'] = true;
        $params['_m_escape'] = '';
        $params['_query']       = array($filter->getRequestVar().$this->getShowAllSuffix() => 1);
        return Mage::helper('mana_filters')->markLayeredNavigationUrl(Mage::getUrl('*/*/*', $params), '*/*/*', $params);
	}
	/**
	 * Returns current URL modified to enable showing truncated item list 
	 * @param Mana_Filters_Model_Filter_Attribute $filter
	 * @return string
	 */
	public function getShowLessUrl($filter) {
    	/* @var $ext Mana_Filters_Helper_Extended */ $ext = Mage::helper(strtolower('Mana_Filters/Extended'));
        $params = array('_secure' => Mage::app()->getFrontController()->getRequest()->isSecure());
		$params['_current']     = true;
        $params['_use_rewrite'] = true;
        $params['_m_escape'] = '';
        $params['_query']       = array($filter->getRequestVar().$this->getShowAllSuffix() => null);
        return Mage::helper('mana_filters')->markLayeredNavigationUrl(Mage::getUrl('*/*/*', $params), '*/*/*', $params);
	}
    public function getPopupUrl($filter) {
        $params = array('_secure' => Mage::app()->getFrontController()->getRequest()->isSecure());
        $params['_current'] = true;
        $params['_use_rewrite'] = true;
        $params['_m_escape'] = '';
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper(strtolower('Mana_Core'));
        $params['_query'] = array(
            'm-show-more-popup' => $filter->getFilterOptions()->getId(),
            'm-seo-enabled' => 1, //$core->getRoutePath() != 'catalogsearch/result/index' ? 1 : 0,
            'm-url' => '__0__',
        );
        if ($core->getRoutePath() != 'catalogsearch/result/index') {
            $params['_query']['m-show-more-cat'] = Mage::getSingleton('catalog/layer')->getCurrentCategory()->getId();
        }
        return Mage::helper('mana_filters')->markLayeredNavigationUrl(Mage::getUrl('*/*/*', $params), '*/*/*', $params);
    }
    public function getPopupTargetUrl($filter) {
        $params = array('_secure' => Mage::app()->getFrontController()->getRequest()->isSecure());
        $params['_current'] = true;
        $params['_use_rewrite'] = true;
        $params['_m_escape'] = '';
        $params['_query'] = array('p' => null, $filter->getRequestVar() => '__0__');
        return Mage::helper('mana_filters')->markLayeredNavigationUrl(Mage::getUrl('*/*/*', $params), '*/*/*', $params);
    }

    public function getPopupClearUrl($filter) {
        $params = array('_secure' => Mage::app()->getFrontController()->getRequest()->isSecure());
        $params['_current'] = true;
        $params['_use_rewrite'] = true;
        $params['_m_escape'] = '';
        $params['_query'] = array($filter->getRequestVar() => null);

        return Mage::helper('mana_filters')->markLayeredNavigationUrl(Mage::getUrl('*/*/*', $params), '*/*/*', $params);
    }

    public function getPopupDimensions($items, $maxRowCount, $maxColumnCount) {
        $count = count($items);
        $columnCount = ceil($count / $maxRowCount);
        if ($columnCount > $maxColumnCount) {
            $columnCount = $maxColumnCount;
        }
        $rowCount = ceil($count / $columnCount);
        return array($rowCount, $columnCount);
    }

    /**
     * @deprecated since 14.02.18.18
     * @return int
     */
    public function getMaxRowCount() {
        return Mage::getStoreConfig('mana_filters/show_more_popup/max_rows');
    }

    /**
     * @deprecated since 14.02.18.18
     * @return int
     */
    public function getMaxColumnCount() {
        return Mage::getStoreConfig('mana_filters/show_more_popup/max_columns');
    }
    public function getMethod($block, $filter) {
        $method = $filter->getFilterOptions()->getShowMoreMethod();
        /*if ($showInFilter = $block->getShowInFilter()) {
            if ($node = Mage::getSingleton('manapro_filterpositioning/source_position')->getNode($showInFilter)) {
                $field = $method ? "show_more_{$method}_as" : "show_more_as";
                if (isset($node->$field)) {
                    $method = (string)$node->$field;
                }
            }
        }*/
        return $method;
    }
    public function isMethodDisabled($block, $filter) {
        $method = $this->getMethod($block, $filter);
        if ($showInFilter = $block->getShowInFilter()) {
            if ($node = Mage::getSingleton('manapro_filterpositioning/source_position')->getCurrentNode($showInFilter)) {
                $field = $method ? "show_more_{$method}_disabled" : "show_more_disabled";
                if (!empty($node->$field)) {
                    return true;
                }
            }
        }
        return false;
    }
}