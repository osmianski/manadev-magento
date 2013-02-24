<?php
/**
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Enter description here ...
 * @author Mana Team
 *
 */
class Mana_Core_Model_Object extends Varien_Object {
	protected $_collections = array();
	protected $_arrays = array();
	
	public function getCollectionItems($collection) {
		return $this->_collections[$collection];
	}
	public function getCollectionItem($collection, $key) {
		return $this->_collections[$collection][$key];
	}
	public function hasCollectionItem($collection, $key) {
		return isset($this->_collections[$collection][$key]);
	}
	public function setCollectionItem($collection, $key, $value) {
		$this->_collections[$collection][$key] = $value;
		return $this;
	}
	public function setCollectionItems($collection, $items) {
		$this->_collections[$collection] = $items;
		return $this;
	}
	public function unsCollectionItem($collection, $key) {
		unset($this->_collections[$collection][$key]);
		return $this;
	}
	
	public function getArrayItems($array) {
		return $this->_arrays[$array];
	}
	public function getArrayItem($array, $index) {
		return $this->_arrays[$array][$index];
	}
	public function unsArrayItems($array) {
		$this->_arrays[$array] = array();
		return $this;
	}
	public function unsArrayItem($array, $index) {
		unset($this->_arrays[$array][$index]);
		return $this;
	}
	public function addArrayItem($array, $value) {
		$this->_arrays[$array][] = $value;
		return $this;
	}
	public function setArrayItems($array, $items) {
		$this->_arrays[$array] = $items;
		return $this;
	}
	
	protected function _toArrayExRecursively($array) {
		$result = array();
		foreach ($array as $key => $value) {
			$result[$key] = ($value instanceof Mana_Core_Model_Object) ? 
				array('__M_ARRAY_EX' => $value->toArrayEx(), '__M_CLASS' => get_class($value)) : 
				$value;
		}
		return $result;
	}
	protected function _fromArrayExRecursively($array) {
		$result = array();
		foreach ($array as $key => $value) {
			if (is_array($value) && isset($value['__M_CLASS']) && isset($value['__M_ARRAY_EX'])) {
				$class = $value['__M_CLASS'];
				$object = new $class;
				$object->fromArrayEx($value['__M_ARRAY_EX']);
				$result[$key] = $object;
			}
			else {
				$result[$key] = $value;
			}
		}
		return $result;
	}
	public function toArrayEx() {
        return array(
        	'data' => $this->_toArrayExRecursively($this->_data), 
        	'collections' => $this->_toArrayExRecursively($this->_collections),
        	'arrays' => $this->_toArrayExRecursively($this->_arrays),
        );
	}
	public function fromArrayEx($array) {
		$this->_data = $this->_fromArrayExRecursively($array['data']);
		$this->_collections = $this->_fromArrayExRecursively($array['collections']);
		$this->_arrays = $this->_fromArrayExRecursively($array['arrays']);
		return $this;
	}
	public function toJsonEx() {
		return Zend_Json::encode($this->toArrayEx());
	}
	public function fromJsonEx($json) {
		$this->fromArrayEx(Zend_Json::decode($json));
	}
	public function __call($method, $args) {
		if (substr($method, 0, 3) == 'get') {
			$key = $this->_underscore(substr($method,3));
			if (isset($this->_collections[$key])) {
				return $this->_collections[$key];
			}
			if (isset($this->_collections[$key.'s'])) {
				return $this->_collections[$key.'s'][$args[0]];
			}
			if (isset($this->_arrays[$key])) {
				return $this->_arrays[$key];
			}
			if (isset($this->_arrays[$key.'s'])) {
				return $this->_arrays[$key.'s'][$args[0]];
			}
		}
		elseif (substr($method, 0, 3) == 'has') {
			$key = $this->_underscore(substr($method,3));
			if (isset($this->_collections[$key.'s'])) {
				return isset($this->_collections[$key.'s'][$args[0]]);
			}
		}
		elseif (substr($method, 0, 3) == 'set') {
			$key = $this->_underscore(substr($method,3));
			if (isset($this->_collections[$key])) {
				$this->_collections[$key] = $args[0];
				return $this;
			}
			if (isset($this->_collections[$key.'s'])) {
				$this->_collections[$key.'s'][$args[0]] = $args[1];
				return $this;
			}
			if (isset($this->_arrays[$key])) {
				$this->_arrays[$key] = $args[0];
				return $this;
			}
		}
		elseif (substr($method, 0, 3) == 'add') {
			$key = $this->_underscore(substr($method,3));
			if (isset($this->_arrays[$key.'s'])) {
				$this->_arrays[$key.'s'][] = $args[0];
				return $this;
			}
		}
		elseif (substr($method, 0, 3) == 'uns') {
			$key = $this->_underscore(substr($method,3));
			if (isset($this->_collections[$key.'s'])) {
				unset($this->_collections[$key.'s'][$args[0]]);
				return $this;
			}
			if (isset($this->_arrays[$key])) {
				$this->_arrays[$key] = array();
				return $this;
			}
			if (isset($this->_arrays[$key.'s'])) {
				unset($this->_arrays[$key.'s'][$args[0]]);
				return $this;
			}
		}
		return parent::__call($method, $args);
	}
}