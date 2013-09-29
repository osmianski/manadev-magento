<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterSeoLinks
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * SQL Selects for SEO friendly layered navigation URLs
 * @author Mana Team
 *
 */
class ManaPro_FilterSeoLinks_Resource_Rewrite extends Mage_Core_Model_Mysql4_Url_Rewrite {
	public function isFilterName($name) {
		Mana_Core_Profiler::start('mln'.'::'. __CLASS__ . '::' . __METHOD__ . '::' . $name);
		/* @var $core Mana_Core_Helper_Data */ $core = Mage::helper(strtolower('Mana_Core'));
		/* @var $helper Mana_Filters_Helper_Data */ $helper = Mage::helper(strtolower('Mana_Filters'));
		$result = $core->collectionFind($helper->getFilterOptionsCollection(true), 'code', $name);
		if ($result && $result->getType() == 'category') {
		    $result = false;
		}
		Mana_Core_Profiler::stop('mln' . '::' . __CLASS__ . '::' . __METHOD__ . '::' . $name);
		return $result;
	}
    public function getFilterName($candidates) {
		/* @var $core Mana_Core_Helper_Data */ $core = Mage::helper(strtolower('Mana_Core'));
		/* @var $helper Mana_Filters_Helper_Data */ $helper = Mage::helper(strtolower('Mana_Filters'));
		$collection = $helper->getFilterOptionsCollection(true);
		foreach ($collection as $item) {
		    if (Mage::getStoreConfigFlag('mana_filters/seo/use_label')) {
                if (in_array($item->getLowerCaseName(), $candidates)) {
                    return $item->getCode();
                }
            }
		    else {
                if (in_array(strtolower($item->getCode()), $candidates)) {
                    return $item->getCode();
                }
            }
		}
		return false;
		///* @var $select Varien_Db_Select */ $select = $this->_getReadAdapter()->select();
		//$select 
		//	->from(array('a' => $this->_resources->getTableName('eav_attribute')), 'attribute_code')
		//	->join(array('t' => $this->_resources->getTableName('eav_entity_type')), 't.entity_type_id = a.entity_type_id', null)
		//	->where('a.attribute_code IN (?)', $candidates)
		//	->where('t.entity_type_code = ?', 'catalog_product');
		//return $this->_getReadAdapter()->fetchOne($select);
	}

	public function getCategoryValue($model, $urlKey) {
		Mana_Core_Profiler::start('mln' . '::' . __CLASS__ . '::' . __METHOD__ . '::' . 'select');
		$path = $this->_getReadAdapter()->fetchOne("SELECT path FROM {$this->_resources->getTableName('catalog_category_entity')} WHERE entity_id = ?", $model->getCategoryId());
		foreach (explode('/', $urlKey) as $currentUrlKey) {
            /* @var $select Varien_Db_Select */
            $select = $this->_getReadAdapter()->select();
            $select
                    ->from(array('e' => $this->_resources->getTableName('catalog_category_entity')), array('entity_id', 'path'))
                    ->join(array('v' => $this->_resources->getTableName('catalog_category_entity_varchar')), 'v.entity_id = e.entity_id', null)
                    ->join(array('a' => $this->_resources->getTableName('eav_attribute')), 'a.attribute_id = v.attribute_id', null)
                    ->join(array('t' => $this->_resources->getTableName('eav_entity_type')), 't.entity_type_id = a.entity_type_id', null)
                    ->where('LOWER(v.value) = ?', $currentUrlKey)
                    ->where('t.entity_type_code = ?', 'catalog_category')
                    ->where('a.attribute_code = ?', 'url_key')
                    ->where('e.path LIKE ?', $path . '/%')
                    ->where('v.store_id IN (?)', array(0, $model->getStoreId()));
            $result = $this->_getReadAdapter()->fetchRow($select);
            $path = $result['path'];
        }
		Mana_Core_Profiler::stop('mln' . '::' . __CLASS__ . '::' . __METHOD__ . '::' . 'select');
		return $result['entity_id'];
	}

	public function getFilterValue($model, $name, $candidates) {
		/* @var $core Mana_Core_Helper_Data */ $core = Mage::helper(strtolower('Mana_Core'));
		/* @var $helper Mana_Filters_Helper_Data */ $helper = Mage::helper(strtolower('Mana_Filters'));
		if (!$filter = $core->collectionFind($helper->getFilterOptionsCollection(true), 'code', $name)) {
			return $candidates[0];
		}
		$attribute = $filter->getAttribute();
		///* @var $select Varien_Db_Select */ $select = $this->_getReadAdapter()->select();
		//$select
		//	->from(array('a' => $this->_resources->getTableName('eav_attribute')), array('attribute_id', 'backend_model', 'source_model', 'backend_type', 'frontend_input'))
		//	->join(array('t' => $this->_resources->getTableName('eav_entity_type')), 't.entity_type_id = a.entity_type_id', null)
		//	->where('a.attribute_code = ?', $name)
		//	->where('t.entity_type_code = ?', 'catalog_product');
		//$attribute = $this->_getReadAdapter()->fetchRow($select);
			
		/* @var $select Varien_Db_Select */ $select = $this->_getReadAdapter()->select();
		if ($attribute->getData('backend_model') == 'eav/entity_attribute_backend_array' ||
		    $attribute->getData('source_model') == 'eav/entity_attribute_source_table' ||
		    $attribute->getData('frontend_input') == 'select' && !$attribute->getData('source_model') ||
		    $attribute->getData('frontend_input') == 'multiselect' && !$attribute->getData('source_model'))
		{
			Mana_Core_Profiler::start('mln' . '::' . __CLASS__ . '::' . __METHOD__ . '::' . 'select');
			$select
				->from(array('o' => $this->_resources->getTableName('eav_attribute_option')), 'option_id')
				->joinLeft(array('vg' => $this->_resources->getTableName('eav_attribute_option_value')), 'o.option_id = vg.option_id AND vg.store_id = 0', null)
                ->joinLeft(array('vs' => $this->_resources->getTableName('eav_attribute_option_value')), 'o.option_id = vs.option_id AND vs.store_id = '. Mage::app()->getStore()->getId(), null)
				->where('LOWER(COALESCE(vs.value, vg.value)) IN (?)', $candidates)
				->where('o.attribute_id = ?', $attribute->getId());
			$result = $this->_getReadAdapter()->fetchOne($select);
			Mana_Core_Profiler::stop('mln' . '::' . __CLASS__ . '::' . __METHOD__ . '::' . 'select');
			return $result;
		}
		else {
			return $candidates[0];
		}
	}

	protected $_attributes = array();
	protected function _getAttribute($entityType, $attributeCode, $columns) {
	    $key = $entityType . '-' . $attributeCode . '-' . implode('-', $columns);
	    if (!isset($this->_attributes[$key])){
            $this->_attributes[$key] = $this->_getReadAdapter()->fetchRow($this->_getReadAdapter()->select()
                ->from(array('a' => $this->_resources->getTableName('eav_attribute')), $columns)
                ->join(array('t' => $this->_resources->getTableName('eav_entity_type')), 't.entity_type_id = a.entity_type_id', null)
                ->where('a.attribute_code = ?', $attributeCode)
                ->where('t.entity_type_code = ?', $entityType));
	    }
        return $this->_attributes[$key];
    }
	public function getCategoryLabel($categoryId) {
		/* @var $select Varien_Db_Select */ $select = $this->_getReadAdapter()->select();
        $path = $this->_getReadAdapter()->fetchOne("SELECT path FROM {$this->_resources->getTableName('catalog_category_entity')} WHERE entity_id = ?", $categoryId);
        $attribute = $this->_getAttribute('catalog_category', 'url_key', array('attribute_id', 'backend_type', 'backend_table'));
        $attributeTable = $attribute['backend_table'] ? $attribute['backend_table'] : 'catalog_category_entity_' . $attribute['backend_type'];
        $currentCategoryId = Mage::helper('mana_filters')->getLayer()->getCurrentCategory()->getId();
        $path = explode('/', $path);
        $relativePath = array_slice($path, array_search($currentCategoryId, $path) + 1);
        $select
			->from(array('e' => $this->_resources->getTableName('catalog_category_entity')), 'e.entity_id')
			->join(array('v' => $this->_resources->getTableName($attributeTable )), 'v.entity_id = e.entity_id', 'LOWER(value)')
			->where('e.entity_id IN (?)', $relativePath)
			->where('v.attribute_id = ?', $attribute['attribute_id'])
			->where('v.store_id IN (?)', array(0, Mage::app()->getStore()->getId()));
		$urlKeys = $this->_getReadAdapter()->fetchPairs($select);
		$result = array();
		foreach ($relativePath as $categoryId) {
		    $result[] = $urlKeys[$categoryId];
		}
		return implode('/', $result);
	}
	public function getFilterValueLabel($code, $value) {
		/* @var $core Mana_Core_Helper_Data */ $core = Mage::helper(strtolower('Mana_Core'));
		/* @var $helper Mana_Filters_Helper_Data */ $helper = Mage::helper(strtolower('Mana_Filters'));
		$attribute = $core->collectionFind($helper->getFilterOptionsCollection(true), 'code', $code)->getAttribute();
		///* @var $select Varien_Db_Select */ $select = $this->_getReadAdapter()->select();
		//$select
		//	->from(array('a' => $this->_resources->getTableName('eav_attribute')), array('attribute_id', 'backend_model', 'source_model', 'backend_type', 'frontend_input'))
		//	->join(array('t' => $this->_resources->getTableName('eav_entity_type')), 't.entity_type_id = a.entity_type_id', null)
		//	->where('a.attribute_code = ?', $code)
		//	->where('t.entity_type_code = ?', 'catalog_product');
		//$attribute = $this->_getReadAdapter()->fetchRow($select);
			
		/* @var $select Varien_Db_Select */ $select = $this->_getReadAdapter()->select();
		if ($attribute->getData('backend_model') == 'eav/entity_attribute_backend_array' || $attribute->getData('source_model') == 'eav/entity_attribute_source_table' || $attribute->getData('frontend_input') == 'select' && !$attribute->getData('source_model')) {
			/* @var $core Mana_Core_Helper_Data */ $core = Mage::helper(strtolower('Mana_Core'));
			$select
				->from(array('o' => $this->_resources->getTableName('eav_attribute_option')), null)
				->joinLeft(array('vg' => $this->_resources->getTableName('eav_attribute_option_value')), 'o.option_id = vg.option_id AND vg.store_id = 0', null)
                ->joinLeft(array('vs' => $this->_resources->getTableName('eav_attribute_option_value')), 'o.option_id = vs.option_id AND vs.store_id = '. Mage::app()->getStore()->getId(), null)
                ->where('o.option_id = ?', $value)
				->where('o.attribute_id = ?', $attribute->getData('attribute_id'))
				->columns('LOWER(COALESCE(vs.value, vg.value)) AS value');
			return $core->labelToUrl($this->_getReadAdapter()->fetchOne($select));
		}
		else {
			return $value;
		}
	}
}