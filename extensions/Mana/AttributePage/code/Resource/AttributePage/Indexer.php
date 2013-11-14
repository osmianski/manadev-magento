<?php
/** 
 * @category    Mana
 * @package     Mana_AttributePage
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_AttributePage_Resource_AttributePage_Indexer extends Mana_AttributePage_Resource_AttributePage_Abstract {
    /**
     * Resource initialization
     */
    protected function _construct() {
        $this->_setResource('mana_attributepage');
    }

    public function process($params) {
        $this->_calculateFinalGlobalSettings($params);
        $this->_calculateFinalStoreLevelSettings($params);
    }

    public function reindexAll() {
        $this->_calculateFinalGlobalSettings();
        $this->_calculateFinalStoreLevelSettings();
    }

    protected function _calculateFinalGlobalSettings($params = array()) {
        $db = $this->_getWriteAdapter();
        $dbHelper = $this->dbHelper()->setTableAlias('ap_gcs');
        $attrCount = Mana_AttributePage_Model_AttributePage_Abstract::MAX_ATTRIBUTE_COUNT;
        $aggregate = $this->dbAggregateHelper();
        $t = $this->attributePageHelper();

        $titleExpr = $this->coreHelper()->isManadevLayeredNavigationInstalled()
            ? $aggregate->expr("COALESCE(`fX`.`name`, `aX`.`frontend_label`)", $attrCount)
            : $aggregate->expr("`aX`.`frontend_label`", $attrCount);
        $urlKeyExpr = $this->coreHelper()->isManadevSeoInstalled()
            ? $aggregate->glue($aggregate->wrap($this->seoHelper()->seoifyExpr("`X`"), $titleExpr), '-')
            : $aggregate->glue($aggregate->wrap("LOWER(`X`)", $titleExpr), '-');
        $fields = array(
            'is_active' => "`ap_gcs`.`is_active`",
            'title' => "IF({$dbHelper->isCustom(Mana_AttributePage_Model_AttributePage_Abstract::DM_TITLE)},
                `ap_gcs`.`title`,
                CONCAT({$aggregate->glue($titleExpr, ' ')}, '{$t->__(' Products')}')
            )",
            'image' => "`ap_gcs`.`image`",
            'include_in_menu' => "`ap_gcs`.`include_in_menu`",
            'url_key' => "IF({$dbHelper->isCustom(Mana_AttributePage_Model_AttributePage_Abstract::DM_URL_KEY)},
                `ap_gcs`.`url_key`,
                {$urlKeyExpr}
            )",
            'template' => "`ap_gcs`.`template`",
            'show_alphabetic_search' => "`ap_gcs`.`show_alphabetic_search`",
            'page_layout' => "`ap_gcs`.`page_layout`",
            'layout_xml' => "`ap_gcs`.`layout_xml`",
            'custom_design_active_from' => "`ap_gcs`.`custom_design_active_from`",
            'custom_design_active_to' => "`ap_gcs`.`custom_design_active_to`",
            'custom_design' => "`ap_gcs`.`custom_design`",
            'custom_layout_xml' => "`ap_gcs`.`custom_layout_xml`",
            'meta_keywords' => "IF({$dbHelper->isCustom(Mana_AttributePage_Model_AttributePage_Abstract::DM_META_KEYWORDS)},
                `ap_gcs`.`meta_keywords`,
                {$aggregate->glue($titleExpr, ',')}
            )",
            //'' => "`ap_gcs`.``",
        );
        $fields['description'] =
            "IF({$dbHelper->isCustom(Mana_AttributePage_Model_AttributePage_Abstract::DM_DESCRIPTION)},
                `ap_gcs`.`description`,
                {$fields['title']}
            )";
        $fields['meta_title'] =
            "IF({$dbHelper->isCustom(Mana_AttributePage_Model_AttributePage_Abstract::DM_META_TITLE)},
                `ap_gcs`.`meta_title`,
                {$fields['title']}
            )";
        $fields['meta_title'] =
            "IF({$dbHelper->isCustom(Mana_AttributePage_Model_AttributePage_Abstract::DM_META_TITLE)},
                `ap_gcs`.`meta_title`,
                {$fields['title']}
            )";
        $fields['meta_description'] =
            "IF({$dbHelper->isCustom(Mana_AttributePage_Model_AttributePage_Abstract::DM_META_DESCRIPTION)},
                `ap_gcs`.`meta_description`,
                {$fields['description']}
            )";

        $select = $db->select();
        $select->from(array('ap_gcs' => $this->getTable('mana_attributepage/attributePage_globalCustomSettings')), null);

        $aggregate->joinLeft($select, 'aX', $this->getTable('eav/attribute'), "`aX`.`attribute_id` = `ap_gcs`.`attribute_id_X`", $attrCount);
        if ($this->coreHelper()->isManadevLayeredNavigationInstalled()) {
            $aggregate->joinLeft($select, 'fX', $this->getTable('mana_filters/filter2'), "`fX`.`code` = `aX`.`attribute_code`", $attrCount);
        }
        $select->columns($this->dbHelper()->wrapIntoZendDbExpr($fields));

        // convert SELECT into UPDATE which acts as INSERT on DUPLICATE unique keys
        $selectSql = $select->__toString();
        $sql = $select->insertFromSelect($this->getTable('mana_attributepage/attributePage_global'), array_keys($fields));

        // run the statement
        $db->exec($sql);
    }

    protected function _calculateFinalStoreLevelSettings($params = array()) {
    }

    #region Dependencies
    /**
     * @return Mana_Core_Helper_Db
     */
    public function dbHelper() {
        return Mage::helper('mana_core/db');
    }

    /**
     * @return Mana_Core_Helper_Data
     */
    public function coreHelper() {
        return Mage::helper('mana_core');
    }

    /**
     * @return Mana_Seo_Helper_Data
     */
    public function seoHelper() {
        return Mage::helper('mana_seo');
    }

    /**
     * @return Mana_Core_Helper_Db_Aggregate
     */
    public function dbAggregateHelper() {
        return Mage::helper('mana_core/db_aggregate');
    }

    /**
     * @return Mana_AttributePage_Helper_Data
     */
    public function attributePageHelper() {
        return Mage::helper('mana_attributepage');
    }
    #endregion
}