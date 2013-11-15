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
class Mana_AttributePage_Resource_OptionPage_Indexer extends Mana_AttributePage_Resource_AttributePage_Abstract {
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
        $dbHelper = $this->dbHelper();
        $attrCount = Mana_AttributePage_Model_AttributePage_Abstract::MAX_ATTRIBUTE_COUNT;
        $aggregate = $this->dbAggregateHelper();
        $t = $this->attributePageHelper();

        $seoifyExpr = $this->coreHelper()->isManadevSeoInstalled()
            ? $this->seoHelper()->seoifyExpr("`X`")
            : $dbHelper->seoifyExpr("`X`");
        $titleExpr = $aggregate->expr("`vgX`.`value`", $attrCount);
        $attributeTitleExpr = $this->coreHelper()->isManadevLayeredNavigationInstalled()
            ? $aggregate->expr("COALESCE(`fX`.`name`, `aX`.`frontend_label`)", $attrCount)
            : $aggregate->expr("`aX`.`frontend_label`", $attrCount);
        $urlKeyExpr = "IF(`ap_gcs`.`option_page_include_filter_name`,
            IF (`a1`.`attribute_id` IS NULL,
                {$aggregate->glue($aggregate->wrap("CONCAT(`ap_g`.`url_key`, '-', `X`)", $aggregate->wrap($seoifyExpr, $titleExpr)), '-')},
                {$aggregate->glue($aggregate->concat($aggregate->wrap($seoifyExpr, $attributeTitleExpr), "'-'",
                    $aggregate->wrap($seoifyExpr, $titleExpr)), '-')}
            ),
            {$aggregate->glue($aggregate->wrap($seoifyExpr, $titleExpr), '-')}
        )";
        $fields = array(
            'attribute_page_global_id' => "`ap_g`.`id`",
            'option_page_global_custom_settings_id' => "`op_gcs`.`id`",
            'option_id_0' => "`o0`.`option_id`",
            'option_id_1' => "`o1`.`option_id`",
            'option_id_2' => "`o2`.`option_id`",
            'option_id_3' => "`o3`.`option_id`",
            'option_id_4' => "`o4`.`option_id`",
            'unique_key' => $aggregate->glue($aggregate->expr("`oX`.`option_id`", $attrCount), '-'),
            'is_active' => "IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_IS_ACTIVE)},
                `op_gcs`.`is_active`,
                `ap_gcs`.`option_page_is_active`
            )",
            'title' => "IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_TITLE)},
                `op_gcs`.`title`,
                CONCAT({$aggregate->glue($titleExpr, ' ')}, '{$t->__(' Products')}')
            )",
            'image' => "IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_IMAGE)},
                `op_gcs`.`image`,
                `ap_gcs`.`option_page_image`
            )",
            'include_in_menu' => "IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_INCLUDE_IN_MENU)},
                `op_gcs`.`include_in_menu`,
                `ap_gcs`.`option_page_include_in_menu`
            )",
            'url_key' => "IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_URL_KEY)},
                `op_gcs`.`url_key`,
                {$urlKeyExpr}
            )",
            'show_products' => "IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_SHOW_PRODUCTS)},
                `op_gcs`.`show_products`,
                `ap_gcs`.`option_page_show_products`
            )",
            'available_sort_by' => "IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_AVAILABLE_SORT_BY)},
                `op_gcs`.`available_sort_by`,
                `ap_gcs`.`option_page_available_sort_by`
            )",

            'default_sort_by' => "IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_DEFAULT_SORT_BY)},
                `op_gcs`.`default_sort_by`,
                `ap_gcs`.`option_page_default_sort_by`
            )",
            'price_step' => "IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_PRICE_STEP)},
                `op_gcs`.`price_step`,
                `ap_gcs`.`option_page_price_step`
            )",

            'page_layout' => "IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_PAGE_LAYOUT)},
                `op_gcs`.`page_layout`,
                `ap_gcs`.`option_page_page_layout`
            )",
            'layout_xml' => "IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_LAYOUT_XML)},
                `op_gcs`.`layout_xml`,
                `ap_gcs`.`option_page_layout_xml`
            )",
            'custom_design_active_from' => "IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_CUSTOM_DESIGN_ACTIVE_FROM)},
                `op_gcs`.`custom_design_active_from`,
                `ap_gcs`.`option_page_custom_design_active_from`
            )",
            'custom_design_active_to' => "IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_CUSTOM_DESIGN_ACTIVE_TO)},
                `op_gcs`.`custom_design_active_to`,
                `ap_gcs`.`option_page_custom_design_active_to`
            )",
            'custom_design' => "IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_CUSTOM_DESIGN)},
                `op_gcs`.`custom_design`,
                `ap_gcs`.`option_page_custom_design`
            )",
            'custom_layout_xml' => "IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_CUSTOM_LAYOUT_XML)},
                `op_gcs`.`custom_layout_xml`,
                `ap_gcs`.`option_page_custom_layout_xml`
            )",

            'meta_keywords' => "IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_META_KEYWORDS)},
                `op_gcs`.`meta_keywords`,
                {$aggregate->glue($titleExpr, ',')}
            )",
        );
        $fields['description'] =
            "IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_DESCRIPTION)},
                `op_gcs`.`description`,
                {$fields['title']}
            )";
        $fields['meta_title'] =
            "IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_META_TITLE)},
                `op_gcs`.`meta_title`,
                {$fields['title']}
            )";
        $fields['meta_description'] =
            "IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_META_DESCRIPTION)},
                `op_gcs`.`meta_description`,
                {$fields['description']}
            )";

        $select = $db->select();
        $select
            ->from(array('ap_g' => $this->getTable('mana_attributepage/attributePage_global')), null)
            ->joinInner(array('ap_gcs' => $this->getTable('mana_attributepage/attributePage_globalCustomSettings')),
                "`ap_gcs`.`id` = `ap_g`.`attribute_page_global_custom_settings_id`", null);

        $aggregate->joinLeft($select, 'aX', $this->getTable('eav/attribute'),
            "`aX`.`attribute_id` = `ap_gcs`.`attribute_id_X`", $attrCount);
        if ($this->coreHelper()->isManadevLayeredNavigationInstalled()) {
            $aggregate->joinLeft($select, 'fX', $this->getTable('mana_filters/filter2'),
                "`fX`.`code` = `aX`.`attribute_code`", $attrCount);
        }
        $aggregate->joinLeft($select, 'oX', $this->getTable('eav/attribute_option'),
            "`oX`.`attribute_id` = `aX`.`attribute_id`", $attrCount);
        $aggregate->joinLeft($select, 'vgX', $this->getTable('eav/attribute_option_value'),
            "`vgX`.`option_id` = `oX`.`option_id` AND `vgX`.`store_id` = 0", $attrCount);
        $select->joinLeft(array('op_gcs' => $this->getTable('mana_attributepage/optionPage_globalCustomSettings')),
            "`op_gcs`.`attribute_page_global_id` = `ap_g`.`id`
            AND `op_gcs`.`option_id_0` = `o0`.`option_id`
            AND `op_gcs`.`option_id_1` = `o1`.`option_id`
            AND `op_gcs`.`option_id_2` = `o2`.`option_id`
            AND `op_gcs`.`option_id_3` = `o3`.`option_id`
            AND `op_gcs`.`option_id_4` = `o4`.`option_id`", null);

        $select->columns($this->dbHelper()->wrapIntoZendDbExpr($fields));

        // convert SELECT into UPDATE which acts as INSERT on DUPLICATE unique keys
        $selectSql = $select->__toString();
        $sql = $select->insertFromSelect($this->getTable('mana_attributepage/optionPage_global'), array_keys($fields));

        // run the statement
        $db->exec($sql);
    }

    protected function _calculateFinalStoreLevelSettings($params = array()) {
        $db = $this->_getWriteAdapter();
        $dbHelper = $this->dbHelper();
        $attrCount = Mana_AttributePage_Model_AttributePage_Abstract::MAX_ATTRIBUTE_COUNT;
        $aggregate = $this->dbAggregateHelper();
        $t = $this->attributePageHelper();

        foreach (Mage::app()->getStores() as $store) {
            /* @var $store Mage_Core_Model_Store */
            $schema = $this->coreHelper()->isManadevSeoInstalled()
                ? $this->seoHelper()->getActiveSchema($store->getId())
                : false;
            $seoifyExpr = $this->coreHelper()->isManadevSeoInstalled()
                ? $this->seoHelper()->seoifyExpr("`X`", $schema)
                : $dbHelper->seoifyExpr("`X`");

            $titleExpr = $aggregate->expr("COALESCE(`vsX`.`value`, `vgX`.`value`)", $attrCount);
            $attributeTitleExpr = $this->coreHelper()->isManadevLayeredNavigationInstalled()
                ? $aggregate->expr("COALESCE(`fsX`.`name`, `lX`.`value`, `aX`.`frontend_label`)", $attrCount)
                : $aggregate->expr("COALESCE(`lX`.`value`, `aX`.`frontend_label`)", $attrCount);
            $urlKeyExpr = "IF(`ap_s`.`option_page_include_filter_name`,
                IF (`a1`.`attribute_id` IS NULL,
                    {$aggregate->glue($aggregate->wrap("CONCAT(`ap_s`.`url_key`, '-', `X`)", $aggregate->wrap($seoifyExpr, $titleExpr)), '-')},
                    {$aggregate->glue($aggregate->concat($aggregate->wrap($seoifyExpr, $attributeTitleExpr), "'-'",
                        $aggregate->wrap($seoifyExpr, $titleExpr)), '-')}
                ),
                {$aggregate->glue($aggregate->wrap($seoifyExpr, $titleExpr), '-')}
            )";
            $fields = array(
                'option_page_global_id' => "`op_g`.`id`",
                'store_id' => $store->getId(),
                'option_page_store_custom_settings_id' => "`op_scs`.`id`",
                'is_active' => "IF({$dbHelper->isCustom('op_scs', Mana_AttributePage_Model_OptionPage_Abstract::DM_IS_ACTIVE)},
                    `op_scs`.`is_active`,
                    IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_IS_ACTIVE)},
                        `op_g`.`is_active`,
                        `ap_s`.`option_page_is_active`
                    )
                )",
                'title' => "IF({$dbHelper->isCustom('op_scs', Mana_AttributePage_Model_OptionPage_Abstract::DM_TITLE)},
                    `op_scs`.`title`,
                    IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_TITLE)},
                        `op_g`.`title`,
                        CONCAT({$aggregate->glue($titleExpr, ' ')}, '{$t->__(' Products')}')
                    )
                )",
                'image' => "IF({$dbHelper->isCustom('op_scs', Mana_AttributePage_Model_OptionPage_Abstract::DM_IMAGE)},
                    `op_scs`.`image`,
                    IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_IMAGE)},
                        `op_g`.`image`,
                        `ap_s`.`option_page_image`
                    )
                )",
                'include_in_menu' => "IF({$dbHelper->isCustom('op_scs', Mana_AttributePage_Model_OptionPage_Abstract::DM_INCLUDE_IN_MENU)},
                    `op_scs`.`include_in_menu`,
                    IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_INCLUDE_IN_MENU)},
                        `op_g`.`include_in_menu`,
                        `ap_s`.`option_page_include_in_menu`
                    )
                )",
                'url_key' => "IF({$dbHelper->isCustom('op_scs', Mana_AttributePage_Model_OptionPage_Abstract::DM_URL_KEY)},
                    `op_scs`.`url_key`,
                    IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_URL_KEY)},
                        `op_gcs`.`url_key`,
                        {$urlKeyExpr}
                    )
                )",
                'show_products' => "IF({$dbHelper->isCustom('op_scs', Mana_AttributePage_Model_OptionPage_Abstract::DM_SHOW_PRODUCTS)},
                    `op_scs`.`show_products`,
                    IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_SHOW_PRODUCTS)},
                        `op_g`.`show_products`,
                        `ap_s`.`option_page_show_products`
                    )
                )",
                'available_sort_by' => "IF({$dbHelper->isCustom('op_scs', Mana_AttributePage_Model_OptionPage_Abstract::DM_AVAILABLE_SORT_BY)},
                    `op_scs`.`available_sort_by`,
                    IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_AVAILABLE_SORT_BY)},
                        `op_g`.`available_sort_by`,
                        `ap_s`.`option_page_available_sort_by`
                    )
                )",

                'default_sort_by' => "IF({$dbHelper->isCustom('op_scs', Mana_AttributePage_Model_OptionPage_Abstract::DM_DEFAULT_SORT_BY)},
                    `op_scs`.`default_sort_by`,
                    IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_DEFAULT_SORT_BY)},
                        `op_g`.`default_sort_by`,
                        `ap_s`.`option_page_default_sort_by`
                    )
                )",
                'price_step' => "IF({$dbHelper->isCustom('op_scs', Mana_AttributePage_Model_OptionPage_Abstract::DM_PRICE_STEP)},
                    `op_scs`.`price_step`,
                    IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_PRICE_STEP)},
                        `op_g`.`price_step`,
                        `ap_s`.`option_page_price_step`
                    )
                )",

                'page_layout' => "IF({$dbHelper->isCustom('op_scs', Mana_AttributePage_Model_OptionPage_Abstract::DM_PAGE_LAYOUT)},
                    `op_scs`.`page_layout`,
                    IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_PAGE_LAYOUT)},
                        `op_g`.`page_layout`,
                        `ap_s`.`option_page_page_layout`
                    )
                )",
                'layout_xml' => "IF({$dbHelper->isCustom('op_scs', Mana_AttributePage_Model_OptionPage_Abstract::DM_LAYOUT_XML)},
                    `op_scs`.`layout_xml`,
                    IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_LAYOUT_XML)},
                        `op_g`.`layout_xml`,
                        `ap_s`.`option_page_layout_xml`
                    )
                )",
                'custom_design_active_from' => "IF({$dbHelper->isCustom('op_scs', Mana_AttributePage_Model_OptionPage_Abstract::DM_CUSTOM_DESIGN_ACTIVE_FROM)},
                    `op_scs`.`custom_design_active_from`,
                    IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_CUSTOM_DESIGN_ACTIVE_FROM)},
                        `op_g`.`custom_design_active_from`,
                        `ap_s`.`option_page_custom_design_active_from`
                    )
                )",
                'custom_design_active_to' => "IF({$dbHelper->isCustom('op_scs', Mana_AttributePage_Model_OptionPage_Abstract::DM_CUSTOM_DESIGN_ACTIVE_TO)},
                    `op_scs`.`custom_design_active_to`,
                    IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_CUSTOM_DESIGN_ACTIVE_TO)},
                        `op_g`.`custom_design_active_to`,
                        `ap_s`.`option_page_custom_design_active_to`
                    )
                )",
                'custom_design' => "IF({$dbHelper->isCustom('op_scs', Mana_AttributePage_Model_OptionPage_Abstract::DM_CUSTOM_DESIGN)},
                    `op_scs`.`custom_design`,
                    IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_CUSTOM_DESIGN)},
                        `op_g`.`custom_design`,
                        `ap_s`.`option_page_custom_design`
                    )
                )",
                'custom_layout_xml' => "IF({$dbHelper->isCustom('op_scs', Mana_AttributePage_Model_OptionPage_Abstract::DM_CUSTOM_LAYOUT_XML)},
                    `op_scs`.`custom_layout_xml`,
                    IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_CUSTOM_LAYOUT_XML)},
                        `op_g`.`custom_layout_xml`,
                        `ap_s`.`option_page_custom_layout_xml`
                    )
                )",

                'meta_keywords' => "IF({$dbHelper->isCustom('op_scs', Mana_AttributePage_Model_OptionPage_Abstract::DM_META_KEYWORDS)},
                    `op_scs`.`meta_keywords`,
                    IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_META_KEYWORDS)},
                        `op_g`.`meta_keywords`,
                        {$aggregate->glue($titleExpr, ',')}
                    )
                )",
            );
            $fields['description'] =
                "IF({$dbHelper->isCustom('op_scs', Mana_AttributePage_Model_OptionPage_Abstract::DM_DESCRIPTION)},
                    `op_scs`.`description`,
                    IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_DESCRIPTION)},
                        `op_g`.`description`,
                        {$fields['title']}
                    )
                )";
            $fields['meta_title'] =
                "IF({$dbHelper->isCustom('op_scs', Mana_AttributePage_Model_OptionPage_Abstract::DM_META_TITLE)},
                    `op_scs`.`meta_title`,
                    IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_META_TITLE)},
                        `op_g`.`meta_title`,
                        {$fields['title']}
                    )
                )";
            $fields['meta_description'] =
                "IF({$dbHelper->isCustom('op_scs', Mana_AttributePage_Model_OptionPage_Abstract::DM_META_DESCRIPTION)},
                    `op_scs`.`meta_description`,
                    IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_META_DESCRIPTION)},
                        `op_g`.`meta_description`,
                        {$fields['description']}
                    )
                )";

            $select = $db->select();
            $select
                ->from(array('op_g' => $this->getTable('mana_attributepage/optionPage_global')), null)
                ->joinInner(array('ap_g' => $this->getTable('mana_attributepage/attributePage_global')),
                    "`ap_g`.`id` = `op_g`.`attribute_page_global_id`", null)
                ->joinInner(array('ap_gcs' => $this->getTable('mana_attributepage/attributePage_globalCustomSettings')),
                    "`ap_gcs`.`id` = `ap_g`.`attribute_page_global_custom_settings_id`", null)
                ->joinInner(array('ap_s' => $this->getTable('mana_attributepage/attributePage_store')),
                    $db->quoteInto("`ap_s`.`attribute_page_global_id` = `ap_g`.`id` AND `ap_s`.`store_id` = ?", $store->getId()), null)
                ->joinLeft(array('op_gcs' => $this->getTable('mana_attributepage/optionPage_globalCustomSettings')),
                    "`op_gcs`.`id` = `op_g`.`option_page_global_custom_settings_id`", null)
                ->joinLeft(array('op_scs' => $this->getTable('mana_attributepage/optionPage_storeCustomSettings')),
                    $db->quoteInto("`op_scs`.`option_page_global_id` = `op_g`.`id` AND `op_scs`.`store_id` = ?", $store->getId()), null);

            $aggregate->joinLeft($select, 'aX', $this->getTable('eav/attribute'),
                "`aX`.`attribute_id` = `ap_gcs`.`attribute_id_X`", $attrCount);
            $aggregate->joinLeft($select, 'lX', $this->getTable('eav/attribute_label'),
                $db->quoteInto("`lX`.`attribute_id` = `aX`.`attribute_id` AND `lX`.`store_id` = ?", $store->getId()), $attrCount);
            if ($this->coreHelper()->isManadevLayeredNavigationInstalled()) {
                $aggregate->joinLeft($select, 'fX', $this->getTable('mana_filters/filter2'),
                    "`fX`.`code` = `aX`.`attribute_code`", $attrCount);
                $aggregate->joinLeft($select, 'fsX', $this->getTable('mana_filters/filter2_store'),
                    $db->quoteInto("`fsX`.`global_id` = `fX`.`id` AND `fsX`.`store_id` = ?", $store->getId()), $attrCount);
            }
            $aggregate->joinLeft($select, 'oX', $this->getTable('eav/attribute_option'),
                "`oX`.`option_id` = `op_g`.`option_id_X`", $attrCount);
            $aggregate->joinLeft($select, 'vgX', $this->getTable('eav/attribute_option_value'),
                "`vgX`.`option_id` = `oX`.`option_id` AND `vgX`.`store_id` = 0", $attrCount);
            $aggregate->joinLeft($select, 'vsX', $this->getTable('eav/attribute_option_value'),
                $db->quoteInto("`vsX`.`option_id` = `oX`.`option_id` AND `vsX`.`store_id` = ?", $store->getId()), $attrCount);

            $select->columns($this->dbHelper()->wrapIntoZendDbExpr($fields));

            // convert SELECT into UPDATE which acts as INSERT on DUPLICATE unique keys
            $selectSql = $select->__toString();
            $sql = $select->insertFromSelect($this->getTable('mana_attributepage/optionPage_store'), array_keys($fields));

            // run the statement
            $db->exec($sql);
        }
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