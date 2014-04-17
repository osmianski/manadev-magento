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
class ManaPro_FilterContent_Resource_Option extends Mage_Core_Model_Mysql4_Abstract {
    public function getDefaultOptionLabel($optionId) {
        $db = $this->getReadConnection();

        $select = $db->select()
            ->from(array('ov' => $this->getTable('eav/attribute_option_value')), array('value'))
            ->where("`ov`.`option_id` = ?", $optionId)
            ->where("`ov`.`store_id` = 0");

        return $db->fetchOne($select);
    }

    protected function _construct() {
        $this->_setResource('catalog');
    }

    public function getOptionActions($optionIds) {
        $db = $this->getReadConnection();

        $select = $db->select();

        $filterPositionExpr = $this->coreHelper()->isManadevSeoInstalled() ? "fs.url_position ASC" : "fs.position ASC";
        $select
            ->from(array('fvs' => $this->getTable('mana_filters/filter2_value_store')), array(
                'option_id',
                'content_is_active',
                'content_stop_further_processing',
                'content_layout_xml',
                'content_widget_layout_xml',
                'content_meta_title',
                'content_meta_keywords',
                'content_meta_description',
                'content_meta_robots',
                'content_title',
                'content_subtitle',
                'content_description',
                'content_additional_description',
                'content_common_directives',
                'content_background_image',
            ))
            ->joinInner(array('fs' => $this->getTable('mana_filters/filter2_store')),
                "`fs`.`id` = `fvs`.`filter_id`", null)
            ->joinInner(array('o' => $this->getTable('eav/attribute_option')),
                "`o`.`option_id` = `fvs`.`option_id`", null)
            ->where("`fvs`.`option_id` IN (?)", $optionIds)
            ->where("`fvs`.`store_id` = ?", Mage::app()->getStore()->getId())
            ->where("`fvs`.`edit_status` = 0")
            ->where("`fvs`.`content_is_active` = 1")
            ->where("`fvs`.`content_is_initialized` = 1")
            ->order(array("fvs.content_priority", $filterPositionExpr, "o.sort_order ASC"));

        //$sql = $select->__toString();
        return $db->fetchAssoc($select);
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