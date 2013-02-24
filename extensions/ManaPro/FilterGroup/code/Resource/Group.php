<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterGroup
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_FilterGroup_Resource_Group extends Mage_Core_Model_Mysql4_Abstract {
    /**
     * Resource initialization
     */
    protected function _construct() {
    }

    /**
     * Retrieve connection for read data
     * @return  Varien_Db_Adapter_Pdo_Mysql
     */
    protected function _getReadAdapter() {
        return Mage::getSingleton('core/resource')->getConnection('read');
    }

    /**
     * Retrieve connection for write data
     * @return Zend_Db_Adapter_Abstract
     */
    protected function _getWriteAdapter() {
        return Mage::getSingleton('core/resource')->getConnection('write');
    }

    public function getFilterableAttributeGroups() {
        /* @var $names Mage_Core_Model_Resource */ $names = Mage::getSingleton('core/resource');
        return $this->_getReadAdapter()->fetchAssoc("
          SELECT DISTINCT a.attribute_code, g.attribute_group_name, g.sort_order, g.attribute_group_id
          FROM {$names->getTableName('eav_attribute')} AS a
          INNER JOIN {$names->getTableName('eav_entity_attribute')} AS ea ON a.attribute_id = ea.attribute_id
          INNER JOIN {$names->getTableName('eav_attribute_group')} AS g ON ea.attribute_group_id = g.attribute_group_id
          INNER JOIN {$names->getTableName('catalog_eav_attribute')} AS ca ON ca.attribute_id = a.attribute_id
          WHERE (ca.is_filterable <> 0)
        ");
    }
}