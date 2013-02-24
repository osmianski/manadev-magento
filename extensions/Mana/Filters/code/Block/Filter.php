<?php
/**
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Block type for showing options for filter based on custom attribute
 * @author Mana Team
 * Injected into layout instead of standard catalog/layer_filter_attribute in Mana_Filters_Block_View_Category::_initBlocks.
 */
class Mana_Filters_Block_Filter extends Mage_Catalog_Block_Layer_Filter_Abstract {
    protected $_isBlockPrepared = false;
    /** 
     * This function is typically called to initialize underlying model of filter and apply it to current 
     * product set if needed. Here we leave it as is except that we assign template file here not in constructor,
     * not how standard Magento does.
     * @see Mage_Catalog_Block_Layer_Filter_Abstract::init()
     */
    public function init() {
        /* @var $helper Mana_Filters_Helper_Data */
        $helper = Mage::helper(strtolower('Mana_Filters'));

    	$this->setTemplate((string)$this->getDisplayOptions()->template); 
        $this->_filterModelName = $helper->getFilterTypeName('model', $this->getFilterOptions());
    	return parent::init();
    }
    
    protected function _prepareFilter() {
    	if ($this->getAttributeModel()) {
        	$this->_filter->setAttributeModel($this->getAttributeModel());
    	}
    	$this->_filter
    		->setFilterOptions($this->getFilterOptions())
    		->setDisplayOptions($this->getDisplayOptions())
    		->setMode($this->getMode())
    		->setQuery($this->getQuery());
    	return $this;
    }

    protected function _prepareFilterBlock() {
        return $this;
    }

    /**
     * Returns underlying model object which contains actual filter data
     * @return Mage_Catalog_Model_Layer_Filter_Attribute
     */
    public function getFilter() {
    	return $this->_filter;
    }
    
    public function getName() {
        if ($parent = $this->getParentBlock()) {
            if ($label = $parent->getData($this->getFilterOptions()->getCode().'_label')) {
                return $label;
            }
        }

    	return $this->getFilterOptions()->getName();
    }

    public function getSelectedSeoValues() {
        $result = array();
        foreach ($this->getItems() as $item) {
            /* @var $item Mana_Filters_Model_Item */
            if ($item->getMSelected()) {
                $result[] = $item->getSeoValue();
            }
        }
        return implode('_', $result);
    }

    public function getItemsCount() {
        $this->_prepareFilterBlockOnce();
        return $this->getHidden() ? 0 : $this->_filter->getItemsCount();
    }

    protected function _initFilter() {
        if ($filter = Mage::getSingleton('mana_filters/repository')->getFilter($this->getFilterOptions()->getCode())) {
            $this->_filter = $filter;
        }
        else {
            if (!$this->_filterModelName) {
                Mage::throwException(Mage::helper('catalog')->__('Filter model name must be declared.'));
            }
            $this->_filter = Mage::getModel($this->_filterModelName)
                    ->setLayer($this->getLayer());
            $this->_prepareFilter();

            //$this->_filter->apply($this->getRequest(), $this);
            $this->getQuery()->addFilter($this->getFilterOptions()->getCode(), $this->_filter);
            Mage::getSingleton('mana_filters/repository')->setFilter($this->getFilterOptions()->getCode(), $this->_filter);
        }
        return $this;
    }

    protected function _prepareFilterBlockOnce() {
        if (!$this->_isBlockPrepared) {
            $this->_prepareFilterBlock();
            $this->_isBlockPrepared = true;
        }
        return $this;
    }
}