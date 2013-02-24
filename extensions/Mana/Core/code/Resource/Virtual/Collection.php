<?php
/**
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Base class for virtual collections (not based on SELECT SQL statement). All items must be Varien_Object derived 
 * @author Mana Team
 *
 */
class Mana_Core_Resource_Virtual_Collection extends Varien_Data_Collection {
    public function load($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }
    	
        $this
        	->_loadCustomItems()
        	->_addMissingOriginalItems()
        	->_renderFilters()
        	->_renderOrders();
        
        // calculate totals
        $this->_totalRecords = count($this->_items);
        $this->_renderLimit();
        $this->_setIsLoaded();
            	
        return $this;
    }
    
    protected function _loadCustomItems() {
    	return $this;
    }
    protected function _addMissingOriginalItems() {
    	return $this;
    }
	protected function _renderFilters() {
		/* @var $core Mana_Core_Helper_Data */ $core = Mage::helper(strtolower('Mana_Core'));
		$items = array();
		foreach ($this->_items as $key => $item) {
			$conforms = true;
			foreach ($this->_mFilters as $filter) {
				if ($filter['attribute'] == 'entity_type_id') continue;
				
				$method = 'get'.$core->pascalCased($filter['attribute']);
				$value = $item->$method();
				if (isset($filter['condition']['like'])) {
					$value = mb_convert_case($value, MB_CASE_UPPER, "UTF-8");
					$test = mb_convert_case($filter['condition']['like'], MB_CASE_UPPER, "UTF-8");
					if (mb_strpos($value, mb_substr($test, 1, mb_strlen($test) - 2)) === false) {
						$conforms = false;
						break;
					}
				}
				elseif (isset($filter['condition']['eq'])) {
					$test = $filter['condition']['eq'];
					if ($value != $test) {
						$conforms = false;
						break;
					}
				}
			}
			if ($conforms) {
				$items[$key] = $item;
			}
		}
		$this->_items = $items;
		return $this;
	}
	protected function _renderLimit() {
		if ($this->_pageSize !== false) {
			$items = array();
			$index = 0;
			$from = $this->_pageSize * ($this->_curPage - 1);
			$to = $this->_pageSize * $this->_curPage;
			foreach ($this->_items as $key => $item) {
				if ($from <= $index && $index < $to) {
					$items[$key] = $item;
				}
				$index++;
			}
			$this->_items = $items;
		}
		return $this;
	}
	protected function _renderOrders() {
		if ($this->_mOrder) {
			uasort($this->_items, array($this, '_orderCallback'));
		}
		
		return $this;
	}
	
	public function _orderCallback($a, $b) {
		/* @var $core Mana_Core_Helper_Data */ $core = Mage::helper(strtolower('Mana_Core'));
		$method = 'get'.$core->pascalCased($this->_mOrder['attribute']);
		$aValue = $a->$method();
		$bValue = $b->$method();
		if ($aValue == $bValue) return 0;
		if (is_string($aValue)) $aValue = mb_convert_case($aValue, MB_CASE_UPPER, "UTF-8");
		if (is_string($bValue)) $bValue = mb_convert_case($bValue, MB_CASE_UPPER, "UTF-8");
		return (strtolower($this->_mOrder['dir']) == 'desc' ? -1 : 1) * (($aValue < $bValue) ? -1 : 1);
	}
	protected $_mOrder;
    public function setOrder($attribute, $dir='desc') {
    	parent::setOrder($attribute, $dir);
    	$this->_mOrder = array('attribute' => $attribute, 'dir' => $dir);
    	return $this;
    }
    protected $_mFilters = array();
    public function addAttributeToFilter($attribute, $condition=null, $joinType='inner') {
    	$this->_mFilters[] = array('attribute' => $attribute, 'condition' => $condition);
    }
    public function addFieldToFilter($attribute, $condition=null) {
    	$this->_mFilters[] = array('attribute' => $attribute, 'condition' => $condition);
    }
}