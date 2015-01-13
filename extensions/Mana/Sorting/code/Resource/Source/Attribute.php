<?php
/** 
 * @category    Mana
 * @package     Mana_Sorting
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Sorting_Resource_Source_Attribute extends Mage_Core_Model_Mysql4_Abstract {
    const FIELDS_LABEL = 'label';
    const FIELDS_OTHER = 'other';

    /**
     * Resource initialization
     */
    protected function _construct() {
        $this->_setResource('catalog');
    }

    public function getAttributes($fields) {
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
                $positionExpr = "COALESCE(`f`.`position`, `additional_table`.`position`, 0)";
            }
            else {
                $labelExpr = "main_table.frontend_label";
                $positionExpr = "COALESCE(`additional_table`.`position`, 0)";
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
                $positionExpr = "COALESCE(`fs`.`position`, `additional_table`.`position`, 0)";
            }
            else {
                $labelExpr = "COALESCE(`l`.`value`, `main_table`.`frontend_label`)";
                $positionExpr = "COALESCE(`additional_table`.`position`, 0)";
            }
        }
        $select
            ->distinct(true)
            ->reset('columns')
            ->columns(array('main_table.attribute_id', $labelExpr))
            ->where("main_table.frontend_input NOT IN ('textarea', 'gallery', 'multiselect', 'media_image')")
            ->where("additional_table.used_for_sort_by = ?", 1)
            ->order('main_table.frontend_label ASC');

        if ($fields == self::FIELDS_LABEL) {
            $select->columns(array('main_table.attribute_id', $labelExpr));
            return $db->fetchPairs($select);
        }
        else {
            $select->columns(array('id' => 'main_table.attribute_id', 'position' => $positionExpr));
            return $db->fetchAssoc($select);
        }
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