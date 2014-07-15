<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterContent
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_FilterContent_Resource_Filter extends Mage_Core_Model_Mysql4_Abstract {
    protected function _construct() {
        $this->_setResource('catalog');
    }

    public function getAttributes($attributeId = null) {
        /* @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Attribute_Collection */
        $collection = Mage::getResourceModel('catalog/product_attribute_collection')
            ->setItemObjectClass('catalog/resource_eav_attribute');
        $db = $collection->getConnection();

        $select = $collection->getSelect();

        if ($this->adminHelper()->isGlobal()) {
            if ($this->coreHelper()->isManadevLayeredNavigationInstalled()) {
                $select->joinLeft(array('f' => $collection->getTable('mana_filters/filter2')),
                    "`f`.`code` = `main_table`.`attribute_code`", null);
                $labelExpr = "COALESCE(`f`.`name`, `main_table`.`frontend_label`)";
            }
            else {
                $labelExpr = "main_table.frontend_label";
            }
        }
        else {
            $storeId = $this->adminHelper()->getStore()->getId();
            $select->joinLeft(array('l' => $collection->getTable('eav/attribute_label')),
                $db->quoteInto("`l`.`attribute_id` = `main_table`.`attribute_id` AND `l`.`store_id` = ?", $storeId), null);
            if ($this->coreHelper()->isManadevLayeredNavigationInstalled()) {
                $select->joinLeft(array('f' => $collection->getTable('mana_filters/filter2')),
                    "`f`.`code` = `main_table`.`attribute_code`", null);
                $select->joinLeft(array('fs' => $collection->getTable('mana_filters/filter2_store')),
                    $db->quoteInto("`fs`.`global_id` = `f`.`id` AND `fs`.`store_id` = ?", $storeId), null);
                $labelExpr = "COALESCE(`fs`.`name`, `l`.`value`, `main_table`.`frontend_label`)";
            }
            else {
                $labelExpr = "COALESCE(`l`.`value`, `main_table`.`frontend_label`)";
            }
        }
        $select
            ->distinct(true)
            ->reset('columns')
            ->columns(array(
                'value' => 'main_table.attribute_id',
                'label' => $labelExpr,
                'type' => new Zend_Db_Expr("IF(main_table.frontend_input = 'price', 'price', 'attribute')"),
                'frontend_input' => 'main_table.frontend_input',
            ))
            ->where("additional_table.is_filterable <> 0")
            ->order('main_table.frontend_label ASC');

        if ($attributeId) {
            $select->where("main_table.attribute_id = ?", $attributeId);
        }
        return $db->fetchAssoc($select);
    }

    #region Dependencies

    /**
     * @return Mana_Core_Helper_Data
     */
    public function coreHelper() {
        return Mage::helper('mana_core');
    }
    /**
     * @return Mana_Admin_Helper_Data
     */
    public function adminHelper() {
        return Mage::helper('mana_admin');
    }
    #endregion
}