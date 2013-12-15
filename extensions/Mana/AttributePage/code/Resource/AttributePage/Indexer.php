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
        $dbHelper = $this->dbHelper();
        $attrCount = Mana_AttributePage_Model_AttributePage_Abstract::MAX_ATTRIBUTE_COUNT;
        $aggregate = $this->dbAggregateHelper();
        $t = $this->attributePageHelper();

        $seoifyExpr = $this->coreHelper()->isManadevSeoInstalled()
            ? $this->seoHelper()->seoifyExpr("`X`")
            : $dbHelper->seoifyExpr("`X`");
        $titleExpr = $this->coreHelper()->isManadevLayeredNavigationInstalled()
            ? $aggregate->expr("COALESCE(`fX`.`name`, `aX`.`frontend_label`)", $attrCount)
            : $aggregate->expr("`aX`.`frontend_label`", $attrCount);
        $urlKeyExpr = $aggregate->glue($aggregate->wrap($seoifyExpr, $titleExpr), '-');
        $fields = array(
            'attribute_page_global_custom_settings_id' => "`ap_gcs`.`id`",
            'title' => "IF({$dbHelper->isCustom('ap_gcs', Mana_AttributePage_Model_AttributePage_Abstract::DM_TITLE)},
                `ap_gcs`.`title`,
                CONCAT({$aggregate->glue($titleExpr, ' ')}, '{$t->__(' Products')}')
            )",
            'url_key' => "IF({$dbHelper->isCustom('ap_gcs', Mana_AttributePage_Model_AttributePage_Abstract::DM_URL_KEY)},
                `ap_gcs`.`url_key`,
                {$urlKeyExpr}
            )",
            'meta_keywords' => "IF({$dbHelper->isCustom('ap_gcs', Mana_AttributePage_Model_AttributePage_Abstract::DM_META_KEYWORDS)},
                `ap_gcs`.`meta_keywords`,
                {$aggregate->glue($titleExpr, ',')}
            )",
        );
        $fields['description'] =
            "IF({$dbHelper->isCustom('ap_gcs', Mana_AttributePage_Model_AttributePage_Abstract::DM_DESCRIPTION)},
                `ap_gcs`.`description`,
                {$fields['title']}
            )";
        $fields['meta_title'] =
            "IF({$dbHelper->isCustom('ap_gcs', Mana_AttributePage_Model_AttributePage_Abstract::DM_META_TITLE)},
                `ap_gcs`.`meta_title`,
                {$fields['title']}
            )";
        $fields['meta_description'] =
            "IF({$dbHelper->isCustom('ap_gcs', Mana_AttributePage_Model_AttributePage_Abstract::DM_META_DESCRIPTION)},
                `ap_gcs`.`meta_description`,
                {$fields['title']}
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
            $titleExpr = $this->coreHelper()->isManadevLayeredNavigationInstalled()
                ? $aggregate->expr("COALESCE(`fsX`.`name`, `lX`.`value`, `aX`.`frontend_label`)", $attrCount)
                : $aggregate->expr("COALESCE(`lX`.`value`, `aX`.`frontend_label`)", $attrCount);
            $urlKeyExpr = $aggregate->glue($aggregate->wrap($seoifyExpr, $titleExpr), '-');
            $fields = array(
                'attribute_page_global_id' => "`ap_g`.`id`",
                'store_id' => $store->getId(),
                'attribute_page_store_custom_settings_id' => "`ap_scs`.`id`",
                'is_active' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_IS_ACTIVE)},
                    `ap_scs`.`is_active`,
                    `ap_gcs`.`is_active`
                )",
                'title' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_TITLE)},
                    `ap_scs`.`title`,
                    IF({$dbHelper->isCustom('ap_gcs', Mana_AttributePage_Model_AttributePage_Abstract::DM_TITLE)},
                        `ap_g`.`title`,
                        CONCAT({$aggregate->glue($titleExpr, ' ')}, '{$t->__(' Products')}')
                    )
                )",
                'image' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_IMAGE)},
                    `ap_scs`.`image`,
                    `ap_gcs`.`image`
                )",
                'include_in_menu' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_INCLUDE_IN_MENU)},
                    `ap_scs`.`include_in_menu`,
                    `ap_gcs`.`include_in_menu`
                )",
                'url_key' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_URL_KEY)},
                    `ap_scs`.`url_key`,
                    IF({$dbHelper->isCustom('ap_gcs', Mana_AttributePage_Model_AttributePage_Abstract::DM_URL_KEY)},
                        `ap_g`.`url_key`,
                        {$urlKeyExpr}
                    )
                )",
                'template' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_TEMPLATE)},
                    `ap_scs`.`template`,
                    `ap_gcs`.`template`
                )",
                'show_alphabetic_search' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_SHOW_ALPHABETIC_SEARCH)},
                    `ap_scs`.`show_alphabetic_search`,
                    `ap_gcs`.`show_alphabetic_search`
                )",
                'page_layout' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_PAGE_LAYOUT)},
                    `ap_scs`.`page_layout`,
                    `ap_gcs`.`page_layout`
                )",
                'layout_xml' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_LAYOUT_XML)},
                    `ap_scs`.`layout_xml`,
                    `ap_gcs`.`layout_xml`
                )",
                'custom_design_active_from' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_CUSTOM_DESIGN_ACTIVE_FROM)},
                    `ap_scs`.`custom_design_active_from`,
                    `ap_gcs`.`custom_design_active_from`
                )",
                'custom_design_active_to' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_CUSTOM_DESIGN_ACTIVE_TO)},
                    `ap_scs`.`custom_design_active_to`,
                    `ap_gcs`.`custom_design_active_to`
                )",
                'custom_design' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_CUSTOM_DESIGN)},
                    `ap_scs`.`custom_design`,
                    `ap_gcs`.`custom_design`
                )",
                'custom_layout_xml' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_CUSTOM_LAYOUT_XML)},
                    `ap_scs`.`custom_layout_xml`,
                    `ap_gcs`.`custom_layout_xml`
                )",
                'meta_keywords' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_META_KEYWORDS)},
                    `ap_scs`.`meta_keywords`,
                    IF({$dbHelper->isCustom('ap_gcs', Mana_AttributePage_Model_AttributePage_Abstract::DM_META_KEYWORDS)},
                        `ap_g`.`meta_keywords`,
                        {$aggregate->glue($titleExpr, ',')}
                    )
                )",
                'option_page_include_filter_name' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_INCLUDE_FILTER_NAME)},
                    `ap_scs`.`option_page_include_filter_name`,
                    `ap_gcs`.`option_page_include_filter_name`
                )",
                'option_page_image' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_IMAGE)},
                    `ap_scs`.`option_page_image`,
                    `ap_gcs`.`option_page_image`
                )",
                'option_page_include_in_menu' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_INCLUDE_IN_MENU)},
                    `ap_scs`.`option_page_include_in_menu`,
                    `ap_gcs`.`option_page_include_in_menu`
                )",
                'option_page_is_active' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_IS_ACTIVE)},
                    `ap_scs`.`option_page_is_active`,
                    `ap_gcs`.`option_page_is_active`
                )",
                'option_page_show_products' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_SHOW_PRODUCTS)},
                    `ap_scs`.`option_page_show_products`,
                    `ap_gcs`.`option_page_show_products`
                )",
                'option_page_available_sort_by' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_AVAILABLE_SORT_BY)},
                    `ap_scs`.`option_page_available_sort_by`,
                    `ap_gcs`.`option_page_available_sort_by`
                )",
                'option_page_default_sort_by' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_DEFAULT_SORT_BY)},
                    `ap_scs`.`option_page_default_sort_by`,
                    `ap_gcs`.`option_page_default_sort_by`
                )",
                'option_page_price_step' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_PRICE_STEP)},
                    `ap_scs`.`option_page_price_step`,
                    `ap_gcs`.`option_page_price_step`
                )",
                'option_page_page_layout' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_PAGE_LAYOUT)},
                    `ap_scs`.`option_page_page_layout`,
                    `ap_gcs`.`option_page_page_layout`
                )",
                'option_page_layout_xml' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_LAYOUT_XML)},
                    `ap_scs`.`option_page_layout_xml`,
                    `ap_gcs`.`option_page_layout_xml`
                )",
                'option_page_custom_design_active_from' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_CUSTOM_DESIGN_ACTIVE_FROM)},
                    `ap_scs`.`option_page_custom_design_active_from`,
                    `ap_gcs`.`option_page_custom_design_active_from`
                )",
                'option_page_custom_design_active_to' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_CUSTOM_DESIGN_ACTIVE_TO)},
                    `ap_scs`.`option_page_custom_design_active_to`,
                    `ap_gcs`.`option_page_custom_design_active_to`
                )",
                'option_page_custom_design' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_CUSTOM_DESIGN)},
                    `ap_scs`.`option_page_custom_design`,
                    `ap_gcs`.`option_page_custom_design`
                )",
                'option_page_custom_layout_xml' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_CUSTOM_LAYOUT_XML)},
                    `ap_scs`.`option_page_custom_layout_xml`,
                    `ap_gcs`.`option_page_custom_layout_xml`
                )",
            );
            $fields['description'] =
                "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_DESCRIPTION)},
                    `ap_scs`.`description`,
                    IF({$dbHelper->isCustom('ap_gcs', Mana_AttributePage_Model_AttributePage_Abstract::DM_DESCRIPTION)},
                        `ap_g`.`description`,
                        {$fields['title']}
                    )
                )";
            $fields['meta_title'] =
                "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_META_TITLE)},
                    `ap_scs`.`meta_title`,
                    IF({$dbHelper->isCustom('ap_gcs', Mana_AttributePage_Model_AttributePage_Abstract::DM_META_TITLE)},
                        `ap_g`.`meta_title`,
                        {$fields['title']}
                    )
                )";
            $fields['meta_description'] =
                "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_META_DESCRIPTION)},
                    `ap_scs`.`meta_description`,
                    IF({$dbHelper->isCustom('ap_gcs', Mana_AttributePage_Model_AttributePage_Abstract::DM_META_DESCRIPTION)},
                        `ap_g`.`meta_description`,
                        {$fields['title']}
                    )
                )";

            $select = $db->select();
            $select
                ->from(array('ap_g' => $this->getTable('mana_attributepage/attributePage_global')), null)
                ->joinInner(array('ap_gcs' => $this->getTable('mana_attributepage/attributePage_globalCustomSettings')),
                    "`ap_gcs`.`id` = `ap_g`.`attribute_page_global_custom_settings_id`", null)
                ->joinLeft(array('ap_scs' => $this->getTable('mana_attributepage/attributePage_storeCustomSettings')),
                    $db->quoteInto("`ap_scs`.`attribute_page_global_id` = `ap_g`.`id` AND `ap_scs`.`store_id` = ?", $store->getId()), null);

            $aggregate->joinLeft($select, 'aX', $this->getTable('eav/attribute'), "`aX`.`attribute_id` = `ap_gcs`.`attribute_id_X`", $attrCount);
            $aggregate->joinLeft($select, 'lX', $this->getTable('eav/attribute_label'),
                $db->quoteInto("`lX`.`attribute_id` = `aX`.`attribute_id` AND `lX`.`store_id` = ?", $store->getId()), $attrCount);
            if ($this->coreHelper()->isManadevLayeredNavigationInstalled()) {
                $aggregate->joinLeft($select, 'fX', $this->getTable('mana_filters/filter2'),
                    "`fX`.`code` = `aX`.`attribute_code`", $attrCount);
                $aggregate->joinLeft($select, 'fsX', $this->getTable('mana_filters/filter2_store'),
                    $db->quoteInto("`fsX`.`global_id` = `fX`.`id` AND `fsX`.`store_id` = ?", $store->getId()), $attrCount);
            }
            $select->columns($this->dbHelper()->wrapIntoZendDbExpr($fields));

            // convert SELECT into UPDATE which acts as INSERT on DUPLICATE unique keys
            $selectSql = $select->__toString();
            $sql = $select->insertFromSelect($this->getTable('mana_attributepage/attributePage_store'), array_keys($fields));

            // run the statement
            $db->exec($sql);
        }
    }
}