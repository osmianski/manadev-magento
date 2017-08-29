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

    public function process($options) {
        $this->_calculateFinalGlobalSettings($options);
        $this->_calculateFinalStoreLevelSettings($options);
    }

    public function reindexAll() {
        $this->process(array('reindex_all' => true));
    }

    protected function _calculateFinalGlobalSettings($options) {
        if (isset($options['store_id']) ||
            !isset($options['attribute_id']) &&
            !isset($options['attribute_page_global_custom_settings_id']) &&
            !isset($options['attribute_page_global_id']) &&
            !isset($options['option_page_global_custom_settings_id']) &&
            empty($options['reindex_all'])
        )
        {
            return;
        }

        $db = $this->_getWriteAdapter();
        $dbHelper = $this->dbHelper();
        $attrCount = Mana_AttributePage_Model_AttributePage_Abstract::MAX_ATTRIBUTE_COUNT;
        $aggregate = $this->dbAggregateHelper();

        $seoifyExpr = $this->coreHelper()->isManadevSeoInstalled()
            ? $this->seoHelper()->seoifyExpr("`X`")
            : $dbHelper->seoifyExpr("`X`");
        $titleExpr = $aggregate->expr("`vgX`.`value`", $attrCount);

        $title = array(
            'template' => Mage::getStoreConfig('mana_attributepage/option_page_title/template'),
            'separator' => Mage::getStoreConfig('mana_attributepage/option_page_title/separator'),
            'last_separator' => Mage::getStoreConfig('mana_attributepage/option_page_title/last_separator'),
        );

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
            'all_option_ids' => $aggregate->glue($aggregate->expr("`oX`.`option_id`", $attrCount), '-'),
            'is_active' => "IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_IS_ACTIVE)},
                `op_gcs`.`is_active`,
                `ap_gcs`.`option_page_is_active`
            )",
            'title' => "IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_TITLE)},
                `op_gcs`.`title`,
                {$this->templateHelper()->dbConcat($this->templateHelper()->parse($title['template']), array(
                    'option_labels' => $title['last_separator']
                        ? $aggregate->glue($titleExpr, $title['separator'], $title['last_separator'])
                        : $aggregate->glue($titleExpr, $title['last_separator'])
                ))}
            )",
            'raw_title' => $aggregate->glue($titleExpr, ','),
            'description_position' => "IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_DESCRIPTION_POSITION)},
                `op_gcs`.`description_position`,
                `ap_gcs`.`option_page_description_position`
            )",
            'description_bottom' => "`op_gcs`.`description_bottom`",
            'position' => "IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_POSITION)},
                `op_gcs`.`position`,
                {$aggregate->sum($aggregate->expr("COALESCE(`oX`.`sort_order`, 0)", $attrCount))}
            )",
            'image' => "IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_IMAGE)},
                `op_gcs`.`image`,
                `ap_gcs`.`option_page_image`
            )",
            'image_width' => "IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_IMAGE_WIDTH)},
                `op_gcs`.`image_width`,
                `ap_gcs`.`option_page_image_width`
            )",
            'image_height' => "IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_IMAGE_HEIGHT)},
                `op_gcs`.`image_height`,
                `ap_gcs`.`option_page_image_height`
            )",
            'featured_image' => "IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_FEATURED_IMAGE)},
                `op_gcs`.`featured_image`,
                IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_IMAGE)},
                    `op_gcs`.`image`,
                    `ap_gcs`.`option_page_image`
                )
            )",
            'featured_image_width' => "IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_FEATURED_IMAGE_WIDTH)},
                `op_gcs`.`featured_image_width`,
                `ap_gcs`.`option_page_featured_image_width`
            )",
            'featured_image_height' => "IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_FEATURED_IMAGE_HEIGHT)},
                `op_gcs`.`featured_image_height`,
                `ap_gcs`.`option_page_featured_image_height`
            )",
            'product_image' => "IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_PRODUCT_IMAGE)},
                `op_gcs`.`product_image`,
                IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_IMAGE)},
                    `op_gcs`.`image`,
                    `ap_gcs`.`option_page_image`
                )
            )",
            'product_image_width' => "IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_PRODUCT_IMAGE_WIDTH)},
                `op_gcs`.`product_image_width`,
                `ap_gcs`.`option_page_product_image_width`
            )",
            'product_image_height' => "IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_PRODUCT_IMAGE_HEIGHT)},
                `op_gcs`.`product_image_height`,
                `ap_gcs`.`option_page_product_image_height`
            )",
            'show_product_image' => "IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_SHOW_PRODUCT_IMAGE)},
                `op_gcs`.`show_product_image`,
                `ap_gcs`.`option_page_show_product_image`
            )",
            'sidebar_image' => "IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_SIDEBAR_IMAGE)},
                `op_gcs`.`sidebar_image`,
                IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_IMAGE)},
                    `op_gcs`.`image`,
                    `ap_gcs`.`option_page_image`
                )
            )",
            'sidebar_image_width' => "IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_SIDEBAR_IMAGE_WIDTH)},
                `op_gcs`.`sidebar_image_width`,
                `ap_gcs`.`option_page_sidebar_image_width`
            )",
            'sidebar_image_height' => "IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_SIDEBAR_IMAGE_HEIGHT)},
                `op_gcs`.`sidebar_image_height`,
                `ap_gcs`.`option_page_sidebar_image_height`
            )",
            'include_in_menu' => "IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_INCLUDE_IN_MENU)},
                `op_gcs`.`include_in_menu`,
                `ap_gcs`.`option_page_include_in_menu`
            )",
            'is_featured' => "IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_IS_FEATURED)},
                `op_gcs`.`is_featured`,
                `ap_gcs`.`option_page_is_featured`
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
        $fields['heading'] =
            "IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_HEADING)},
                `op_gcs`.`heading`,
                {$fields['title']}
            )";
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
                {$fields['title']}
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
        $select->where("`o0`.`option_id` IS NOT NULL");

        $aggregate->joinLeft($select, 'vgX', $this->getTable('eav/attribute_option_value'),
            "`vgX`.`option_id` = `oX`.`option_id` AND `vgX`.`store_id` = 0", $attrCount);
        $select->joinLeft(array('op_gcs' => $this->getTable('mana_attributepage/optionPage_globalCustomSettings')),
            "`op_gcs`.`attribute_page_global_id` = `ap_g`.`id`
            AND `op_gcs`.`option_id_0` = `o0`.`option_id`
            AND (`op_gcs`.`option_id_1` = `o1`.`option_id` OR `op_gcs`.`option_id_1` IS NULL)
            AND (`op_gcs`.`option_id_2` = `o2`.`option_id` OR `op_gcs`.`option_id_2` IS NULL)
            AND (`op_gcs`.`option_id_3` = `o3`.`option_id` OR `op_gcs`.`option_id_3` IS NULL)
            AND (`op_gcs`.`option_id_4` = `o4`.`option_id` OR `op_gcs`.`option_id_4` IS NULL)", null);

        $select->columns($this->dbHelper()->wrapIntoZendDbExpr($fields));

        if (isset($options['attribute_id'])) {
            $select->where(implode(' OR ', array(
                $db->quoteInto("(`ap_gcs`.`attribute_id_0` = ?)", $options['attribute_id']),
                $db->quoteInto("(`ap_gcs`.`attribute_id_1` = ? OR `ap_gcs`.`attribute_id_1` IS NULL)", $options['attribute_id']),
                $db->quoteInto("(`ap_gcs`.`attribute_id_2` = ? OR `ap_gcs`.`attribute_id_2` IS NULL)", $options['attribute_id']),
                $db->quoteInto("(`ap_gcs`.`attribute_id_3` = ? OR `ap_gcs`.`attribute_id_3` IS NULL)", $options['attribute_id']),
                $db->quoteInto("(`ap_gcs`.`attribute_id_4` = ? OR `ap_gcs`.`attribute_id_4` IS NULL)", $options['attribute_id']),
            )));
        }

        if (isset($options['attribute_page_global_custom_settings_id'])) {
            $select->where("`ap_gcs`.`id` = ?", $options['attribute_page_global_custom_settings_id']);
        }

        if (isset($options['option_page_global_custom_settings_id'])) {
            $select->where("`op_gcs`.`id` = ?", $options['option_page_global_custom_settings_id']);
        }

        if (isset($options['attribute_page_global_id'])) {
            $select->where("`ap_g`.`id` = ?", $options['attribute_page_global_id']);
        }
        // convert SELECT into UPDATE which acts as INSERT on DUPLICATE unique keys
        $selectSql = $select->__toString();
        $sql = $select->insertFromSelect($this->getTable('mana_attributepage/optionPage_global'), array_keys($fields));

        // run the statement
        $db->exec($sql);
    }

    protected function _calculateFinalStoreLevelSettings($options) {
        if (!isset($options['attribute_id']) &&
            !isset($options['attribute_page_global_custom_settings_id']) &&
            !isset($options['attribute_page_global_id']) &&
            !isset($options['option_page_global_custom_settings_id']) &&
            !isset($options['option_page_global_id']) &&
            !isset($options['store_id']) &&
            empty($options['reindex_all'])
        )
        {
            return;
        }

        $db = $this->_getWriteAdapter();
        $dbHelper = $this->dbHelper();
        $attrCount = Mana_AttributePage_Model_AttributePage_Abstract::MAX_ATTRIBUTE_COUNT;
        $aggregate = $this->dbAggregateHelper();

        foreach (Mage::app()->getStores() as $store) {
            /* @var $store Mage_Core_Model_Store */
            if (isset($options['store_id']) && $store->getId() != $options['store_id']) {
                continue;
            }
            $schema = $this->coreHelper()->isManadevSeoInstalled()
                ? $this->seoHelper()->getActiveSchema($store->getId())
                : false;
            $seoifyExpr = $this->coreHelper()->isManadevSeoInstalled()
                ? $this->seoHelper()->seoifyExpr("`X`", $schema)
                : $dbHelper->seoifyExpr("`X`");

            $titleExpr = $aggregate->expr("COALESCE(`vsX`.`value`, `vgX`.`value`)", $attrCount);
            $title = array(
                'template' => Mage::getStoreConfig('mana_attributepage/option_page_title/template', $store),
                'separator' => Mage::getStoreConfig('mana_attributepage/option_page_title/separator', $store),
                'last_separator' => Mage::getStoreConfig('mana_attributepage/option_page_title/last_separator', $store),
            );
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
                        {$this->templateHelper()->dbConcat($this->templateHelper()->parse($title['template']), array(
                            'option_labels' => $title['last_separator']
                                ? $aggregate->glue($titleExpr, $title['separator'], $title['last_separator'])
                                : $aggregate->glue($titleExpr, $title['last_separator'])
                        ))}
                    )
                )",
                'raw_title' => $aggregate->glue($titleExpr, ','),
                'description_position' => "IF({$dbHelper->isCustom('op_scs', Mana_AttributePage_Model_OptionPage_Abstract::DM_DESCRIPTION_POSITION)},
                    `op_scs`.`description_position`,
                    IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_DESCRIPTION_POSITION)},
                        `op_g`.`description_position`,
                        `ap_s`.`option_page_description_position`
                    )
                )",
                'description_bottom' => "IF({$dbHelper->isCustom('op_scs', Mana_AttributePage_Model_OptionPage_Abstract::DM_DESCRIPTION_BOTTOM)},
                    `op_scs`.`description_bottom`,
                    `op_g`.`description_bottom`
                )",
                'position' => "IF({$dbHelper->isCustom('op_scs', Mana_AttributePage_Model_OptionPage_Abstract::DM_POSITION)},
                    `op_scs`.`position`,
                    IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_POSITION)},
                        `op_g`.`position`,
                        {$aggregate->sum($aggregate->expr("COALESCE(`oX`.`sort_order`, 0)", $attrCount))}
                    )
                )",
                'image' => "IF({$dbHelper->isCustom('op_scs', Mana_AttributePage_Model_OptionPage_Abstract::DM_IMAGE)},
                    `op_scs`.`image`,
                    IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_IMAGE)},
                        `op_g`.`image`,
                        `ap_s`.`option_page_image`
                    )
                )",
                'image_width' => "IF({$dbHelper->isCustom('op_scs', Mana_AttributePage_Model_OptionPage_Abstract::DM_IMAGE_WIDTH)},
                    `op_scs`.`image_width`,
                    IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_IMAGE_WIDTH)},
                        `op_g`.`image_width`,
                        `ap_s`.`option_page_image_width`
                    )
                )",
                'image_height' => "IF({$dbHelper->isCustom('op_scs', Mana_AttributePage_Model_OptionPage_Abstract::DM_IMAGE_HEIGHT)},
                    `op_scs`.`image_height`,
                    IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_IMAGE_HEIGHT)},
                        `op_g`.`image_height`,
                        `ap_s`.`option_page_image_height`
                    )
                )",
                'featured_image' => "IF({$dbHelper->isCustom('op_scs', Mana_AttributePage_Model_OptionPage_Abstract::DM_FEATURED_IMAGE)},
                    `op_scs`.`featured_image`,
                    IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_FEATURED_IMAGE)},
                        `op_g`.`featured_image`,
                        IF({$dbHelper->isCustom('op_scs', Mana_AttributePage_Model_OptionPage_Abstract::DM_IMAGE)},
                            `op_scs`.`image`,
                            IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_IMAGE)},
                                `op_g`.`image`,
                                `ap_s`.`option_page_image`
                            )
                        )
                    )
                )",
                'featured_image_width' => "IF({$dbHelper->isCustom('op_scs', Mana_AttributePage_Model_OptionPage_Abstract::DM_FEATURED_IMAGE_WIDTH)},
                    `op_scs`.`featured_image_width`,
                    IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_FEATURED_IMAGE_WIDTH)},
                        `op_g`.`featured_image_width`,
                        `ap_s`.`option_page_featured_image_width`
                    )
                )",
                'featured_image_height' => "IF({$dbHelper->isCustom('op_scs', Mana_AttributePage_Model_OptionPage_Abstract::DM_FEATURED_IMAGE_HEIGHT)},
                    `op_scs`.`featured_image_height`,
                    IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_FEATURED_IMAGE_HEIGHT)},
                        `op_g`.`featured_image_height`,
                        `ap_s`.`option_page_featured_image_height`
                    )
                )",
                'product_image' => "IF({$dbHelper->isCustom('op_scs', Mana_AttributePage_Model_OptionPage_Abstract::DM_PRODUCT_IMAGE)},
                    `op_scs`.`product_image`,
                    IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_PRODUCT_IMAGE)},
                        `op_g`.`product_image`,
                        IF({$dbHelper->isCustom('op_scs', Mana_AttributePage_Model_OptionPage_Abstract::DM_IMAGE)},
                            `op_scs`.`image`,
                            IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_IMAGE)},
                                `op_g`.`image`,
                                `ap_s`.`option_page_image`
                            )
                        )
                    )
                )",
                'product_image_width' => "IF({$dbHelper->isCustom('op_scs', Mana_AttributePage_Model_OptionPage_Abstract::DM_PRODUCT_IMAGE_WIDTH)},
                    `op_scs`.`product_image_width`,
                    IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_PRODUCT_IMAGE_WIDTH)},
                        `op_g`.`product_image_width`,
                        `ap_s`.`option_page_product_image_width`
                    )
                )",
                'product_image_height' => "IF({$dbHelper->isCustom('op_scs', Mana_AttributePage_Model_OptionPage_Abstract::DM_PRODUCT_IMAGE_HEIGHT)},
                    `op_scs`.`product_image_height`,
                    IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_PRODUCT_IMAGE_HEIGHT)},
                        `op_g`.`product_image_height`,
                        `ap_s`.`option_page_product_image_height`
                    )
                )",
                'show_product_image' => "IF({$dbHelper->isCustom('op_scs', Mana_AttributePage_Model_OptionPage_Abstract::DM_SHOW_PRODUCT_IMAGE)},
                    `op_scs`.`show_product_image`,
                    IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_SHOW_PRODUCT_IMAGE)},
                        `op_g`.`show_product_image`,
                        `ap_s`.`option_page_show_product_image`
                    )
                )",
                'sidebar_image' => "IF({$dbHelper->isCustom('op_scs', Mana_AttributePage_Model_OptionPage_Abstract::DM_SIDEBAR_IMAGE)},
                    `op_scs`.`sidebar_image`,
                    IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_SIDEBAR_IMAGE)},
                        `op_g`.`sidebar_image`,
                        IF({$dbHelper->isCustom('op_scs', Mana_AttributePage_Model_OptionPage_Abstract::DM_IMAGE)},
                            `op_scs`.`image`,
                            IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_IMAGE)},
                                `op_g`.`image`,
                                `ap_s`.`option_page_image`
                            )
                        )
                    )
                )",
                'sidebar_image_width' => "IF({$dbHelper->isCustom('op_scs', Mana_AttributePage_Model_OptionPage_Abstract::DM_SIDEBAR_IMAGE_WIDTH)},
                    `op_scs`.`sidebar_image_width`,
                    IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_SIDEBAR_IMAGE_WIDTH)},
                        `op_g`.`sidebar_image_width`,
                        `ap_s`.`option_page_sidebar_image_width`
                    )
                )",
                'sidebar_image_height' => "IF({$dbHelper->isCustom('op_scs', Mana_AttributePage_Model_OptionPage_Abstract::DM_SIDEBAR_IMAGE_HEIGHT)},
                    `op_scs`.`sidebar_image_height`,
                    IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_SIDEBAR_IMAGE_HEIGHT)},
                        `op_g`.`sidebar_image_height`,
                        `ap_s`.`option_page_sidebar_image_height`
                    )
                )",
                'include_in_menu' => "IF({$dbHelper->isCustom('op_scs', Mana_AttributePage_Model_OptionPage_Abstract::DM_INCLUDE_IN_MENU)},
                    `op_scs`.`include_in_menu`,
                    IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_INCLUDE_IN_MENU)},
                        `op_g`.`include_in_menu`,
                        `ap_s`.`option_page_include_in_menu`
                    )
                )",
                'is_featured' => "IF({$dbHelper->isCustom('op_scs', Mana_AttributePage_Model_OptionPage_Abstract::DM_IS_FEATURED)},
                    `op_scs`.`is_featured`,
                    IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_IS_FEATURED)},
                        `op_g`.`is_featured`,
                        `ap_s`.`option_page_is_featured`
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
            $fields['heading'] =
                "IF({$dbHelper->isCustom('op_scs', Mana_AttributePage_Model_OptionPage_Abstract::DM_HEADING)},
                    `op_scs`.`heading`,
                    IF({$dbHelper->isCustom('op_gcs', Mana_AttributePage_Model_OptionPage_Abstract::DM_HEADING)},
                        `op_g`.`heading`,
                        {$fields['title']}
                    )
                )";
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
                        {$fields['title']}
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

            if (isset($options['attribute_id'])) {
                $select->where(implode(' OR ', array(
                    $db->quoteInto("(`ap_gcs`.`attribute_id_0` = ?)", $options['attribute_id']),
                    $db->quoteInto("(`ap_gcs`.`attribute_id_1` = ? OR `ap_gcs`.`attribute_id_1` IS NULL)", $options['attribute_id']),
                    $db->quoteInto("(`ap_gcs`.`attribute_id_2` = ? OR `ap_gcs`.`attribute_id_2` IS NULL)", $options['attribute_id']),
                    $db->quoteInto("(`ap_gcs`.`attribute_id_3` = ? OR `ap_gcs`.`attribute_id_3` IS NULL)", $options['attribute_id']),
                    $db->quoteInto("(`ap_gcs`.`attribute_id_4` = ? OR `ap_gcs`.`attribute_id_4` IS NULL)", $options['attribute_id']),
                )));
            }

            if (isset($options['attribute_page_global_custom_settings_id'])) {
                $select->where("`ap_gcs`.`id` = ?", $options['attribute_page_global_custom_settings_id']);
            }

            if (isset($options['attribute_page_global_id'])) {
                $select->where("`ap_g`.`id` = ?", $options['attribute_page_global_id']);
            }

            if (isset($options['option_page_global_custom_settings_id'])) {
                $select->where("`op_gcs`.`id` = ?", $options['option_page_global_custom_settings_id']);
            }

            if (isset($options['option_page_global_id'])) {
                $select->where("`op_g`.`id` = ?", $options['option_page_global_id']);
            }

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