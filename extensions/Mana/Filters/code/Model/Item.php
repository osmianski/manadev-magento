<?php
/**
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * In-memory representation of a single option of a filter
 * @method bool getMSelected()
 * @method Mana_Filters_Model_Item setMSelected(bool $value)
 * @author Mana Team
 * Injected instead of standard catalog/layer_filter_item in Mana_Filters_Model_Filter_Attribute::_createItemEx()
 * method.
 */
class Mana_Filters_Model_Item extends Mage_Catalog_Model_Layer_Filter_Item {
    /**
     * Returns URL which should be loaded if person chooses to add this filter item into active filters
     * @return string
     * @see Mage_Catalog_Model_Layer_Filter_Item::getUrl()
     * This method is overridden by copying (method body was pasted from parent class and modified as needed). All
     * changes are marked with comments.
     */
    public function getUrl()
    {
    	// MANA BEGIN: add multivalue filter handling
    	$values = $this->getFilter()->getMSelectedValues(); // this could fail if called from some kind of standard filter
    	if (!$values) $values = array();
    	if (!in_array($this->getValue(), $values)) $values[] = $this->getValue();
    	// MANA END
        
    	$query = array(
        	// MANA BEGIN: save multiple values in URL as concatenated with '_'
            $this->getFilter()->getRequestVar()=>implode('_', $values),
            // MANA_END
            Mage::getBlockSingleton('page/html_pager')->getPageVarName() => null // exclude current page from urls
        );
        $params = array('_current'=>true, '_m_escape' => '', '_use_rewrite'=>true, '_query'=>$query, '_secure' => Mage::app()->getFrontController()->getRequest()->isSecure());
        return Mage::helper('mana_filters')->markLayeredNavigationUrl(Mage::getUrl('*/*/*', $params), '*/*/*', $params);
    }
    
    /**
     * Returns URL which should be loaded if person chooses to add this filter item into active filters
     * @return string
     * @see Mage_Catalog_Model_Layer_Filter_Item::getUrl()
     * This method is overridden by copying (method body was pasted from parent class and modified as needed). All
     * changes are marked with comments.
     */
    public function getReplaceUrl()
    {
    	// MANA BEGIN: add multivalue filter handling
    	$values = array();
    	if (!in_array($this->getValue(), $values)) $values[] = $this->getValue();
    	// MANA END
        
    	$query = array(
        	// MANA BEGIN: save multiple values in URL as concatenated with '_'
            $this->getFilter()->getRequestVar()=>implode('_', $values),
            // MANA_END
            Mage::getBlockSingleton('page/html_pager')->getPageVarName() => null // exclude current page from urls
        );
        $params = array('_current'=>true, '_m_escape' => '', '_use_rewrite'=>true, '_query'=>$query, '_secure' => Mage::app()->getFrontController()->getRequest()->isSecure());
        return Mage::helper('mana_filters')->markLayeredNavigationUrl(Mage::getUrl('*/*/*', $params), '*/*/*', $params);
    }
    /** 
     * Returns URL which should be loaded if person chooses to remove this filter item from active filters
     * @return string
     * @see Mage_Catalog_Model_Layer_Filter_Item::getRemoveUrl()
     * This method is overridden by copying (method body was pasted from parent class and modified as needed). All
     * changes are marked with comments.
     */
    public function getRemoveUrl()
    {
    	// MANA BEGIN: add multivalue filter handling
    	if ($this->hasData('remove_url')) {
    	    return $this->getData('remove_url');
    	}

    	$values = $this->getFilter()->getMSelectedValues(); // this could fail if called from some kind of standard filter
    	if (!$values) $values = array();
    	unset($values[array_search($this->getValue(), $values)]);
    	if (count($values) > 0) {
	    	$query = array(
	            $this->getFilter()->getRequestVar()=>implode('_', $values),
	            Mage::getBlockSingleton('page/html_pager')->getPageVarName() => null // exclude current page from urls
	        );
    	}
    	else {
    		$query = array($this->getFilter()->getRequestVar()=>$this->getFilter()->getResetValue());
    	}
    	// MANA END
    	$params = array('_secure' => Mage::app()->getFrontController()->getRequest()->isSecure());
        $params['_current']     = true;
        $params['_use_rewrite'] = true;
        $params['_m_escape'] = '';
        $params['_query']       = $query;
        return Mage::helper('mana_filters')->markLayeredNavigationUrl(Mage::getUrl('*/*/*', $params), '*/*/*', $params);
    }
	public function getUniqueId($block) {
		/* @var $helper Mana_Filters_Helper_Data */ $helper = Mage::helper(strtolower('Mana_Filters'));
		return 'filter_'.$helper->getFilterName($block, $this->getFilter()).'_'.$this->getValue();
	}

	public function getSeoValue() {
	    $urlValue = $this->getValue();
	    /* @var $core Mana_Core_Helper_Data */ $core = Mage::helper(strtolower('Mana_Core'));
        if (Mage::app()->getRequest()->getParam('m-seo-enabled', true) &&
            ((string)Mage::getConfig()->getNode('modules/ManaPro_FilterSeoLinks/active')) == 'true' &&
            $this->getFilter()->getMode() != 'search'
        )
        {
            $url = Mage::getModel('manapro_filterseolinks/url');
            /* @var $url ManaPro_FilterSeoLinks_Model_Url */
            $urlValue = $url->encodeValue($this->getFilter()->getRequestVar(), $urlValue);
       }
       return $urlValue;
	}
}