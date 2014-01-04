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
        $titleExpr = $this->coreHelper()->isManadevLayeredNavigationInstalled()
            ? $aggregate->expr("COALESCE(`fX`.`name`, `aX`.`frontend_label`)", $attrCount)
            : $aggregate->expr("`aX`.`frontend_label`", $attrCount);
        $positionExpr = $this->coreHelper()->isManadevLayeredNavigationInstalled()
            ? $aggregate->expr("COALESCE(`fX`.`position`, `cX`.`position`, 0)", $attrCount)
            : $aggregate->expr("COALESCE(`cX`.`position`, 0)", $attrCount);

        $title = array(
            'template' => Mage::getStoreConfig('mana_attributepage/attribute_page_title/template'),
            'separator' => Mage::getStoreConfig('mana_attributepage/attribute_page_title/separator'),
            'last_separator' => Mage::getStoreConfig('mana_attributepage/attribute_page_title/last_separator'),
        );

        $urlKeyExpr = $aggregate->glue($aggregate->wrap($seoifyExpr, $titleExpr), '-');
        $fields = array(
            'attribute_page_global_custom_settings_id' => "`ap_gcs`.`id`",
            'all_attribute_ids' => $aggregate->glue($aggregate->expr("`aX`.`attribute_id`", $attrCount), '-'),
            'title' => "IF({$dbHelper->isCustom('ap_gcs', Mana_AttributePage_Model_AttributePage_Abstract::DM_TITLE)},
                `ap_gcs`.`title`,
                {$this->templateHelper()->dbConcat($this->templateHelper()->parse($title['template']), array(
                    'attribute_labels' => $title['last_separator']
                        ? $aggregate->glue($titleExpr, $title['separator'], $title['last_separator'])
                        : $aggregate->glue($titleExpr, $title['last_separator'])
                ))}
            )",
            'raw_title' => $aggregate->glue($titleExpr, ','),
            'url_key' => "IF({$dbHelper->isCustom('ap_gcs', Mana_AttributePage_Model_AttributePage_Abstract::DM_URL_KEY)},
                `ap_gcs`.`url_key`,
                {$urlKeyExpr}
            )",
            'meta_keywords' => "IF({$dbHelper->isCustom('ap_gcs', Mana_AttributePage_Model_AttributePage_Abstract::DM_META_KEYWORDS)},
                `ap_gcs`.`meta_keywords`,
                {$aggregate->glue($titleExpr, ',')}
            )",
            'position' => "IF({$dbHelper->isCustom('ap_gcs', Mana_AttributePage_Model_AttributePage_Abstract::DM_POSITION)},
                `ap_gcs`.`position`,
                {$aggregate->sum($positionExpr)}
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
        $aggregate->joinLeft($select, 'cX', $this->getTable('catalog/eav_attribute'), "`cX`.`attribute_id` = `aX`.`attribute_id`", $attrCount);
        if ($this->coreHelper()->isManadevLayeredNavigationInstalled()) {
            $aggregate->joinLeft($select, 'fX', $this->getTable('mana_filters/filter2'), "`fX`.`code` = `aX`.`attribute_code`", $attrCount);
        }
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

        // convert SELECT into UPDATE which acts as INSERT on DUPLICATE unique keys
        $selectSql = $select->__toString();
        $sql = $select->insertFromSelect($this->getTable('mana_attributepage/attributePage_global'), array_keys($fields));

        // run the statement
        $db->exec($sql);
    }

    protected function _calculateFinalStoreLevelSettings($options) {
        if (!isset($options['attribute_id']) &&
            !isset($options['attribute_page_global_custom_settings_id']) &&
            !isset($options['attribute_page_global_id']) &&
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

            $title = array(
                'template' => Mage::getStoreConfig('mana_attributepage/attribute_page_title/template', $store),
                'separator' => Mage::getStoreConfig('mana_attributepage/attribute_page_title/separator', $store),
                'last_separator' => Mage::getStoreConfig('mana_attributepage/attribute_page_title/last_separator', $store),
            );

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
            $positionExpr = $this->coreHelper()->isManadevLayeredNavigationInstalled()
                ? $aggregate->expr("COALESCE(`fsX`.`position`, `cX`.`position`, 0)", $attrCount)
                : $aggregate->expr("COALESCE(`cX`.`position`, 0)", $attrCount);
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
                        {$this->templateHelper()->dbConcat($this->templateHelper()->parse($title['template']), array(
                            'attribute_labels' => $title['last_separator']
                                ? $aggregate->glue($titleExpr, $title['separator'], $title['last_separator'])
                                : $aggregate->glue($titleExpr, $title['last_separator'])
                        ))}
                    )
                )",
                'raw_title' => $aggregate->glue($titleExpr, ','),
                'description_position' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_DESCRIPTION_POSITION)},
                    `ap_scs`.`description_position`,
                    `ap_gcs`.`description_position`
                )",
                'position' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_POSITION)},
                    `ap_scs`.`position`,
                    IF({$dbHelper->isCustom('ap_gcs', Mana_AttributePage_Model_AttributePage_Abstract::DM_POSITION)},
                        `ap_g`.`position`,
                        {$aggregate->sum($positionExpr)}
                    )
                )",
                'image' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_IMAGE)},
                    `ap_scs`.`image`,
                    `ap_gcs`.`image`
                )",
                'image_width' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_IMAGE_WIDTH)},
                    `ap_scs`.`image_width`,
                    `ap_gcs`.`image_width`
                )",
                'image_height' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_IMAGE_HEIGHT)},
                    `ap_scs`.`image_height`,
                    `ap_gcs`.`image_height`
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
                'show_featured_options' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_SHOW_FEATURED_OPTIONS)},
                    `ap_scs`.`show_featured_options`,
                    `ap_gcs`.`show_featured_options`
                )",
                'column_count' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_COLUMN_COUNT)},
                    `ap_scs`.`column_count`,
                    `ap_gcs`.`column_count`
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
                'option_page_description_position' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_DESCRIPTION_POSITION)},
                    `ap_scs`.`option_page_description_position`,
                    `ap_gcs`.`option_page_description_position`
                )",
                'option_page_include_filter_name' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_INCLUDE_FILTER_NAME)},
                    `ap_scs`.`option_page_include_filter_name`,
                    `ap_gcs`.`option_page_include_filter_name`
                )",
                'option_page_image' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_IMAGE)},
                    `ap_scs`.`option_page_image`,
                    `ap_gcs`.`option_page_image`
                )",
                'option_page_image_width' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_IMAGE_WIDTH)},
                    `ap_scs`.`option_page_image_width`,
                    `ap_gcs`.`option_page_image_width`
                )",
                'option_page_image_height' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_IMAGE_HEIGHT)},
                    `ap_scs`.`option_page_image_height`,
                    `ap_gcs`.`option_page_image_height`
                )",
                'option_page_featured_image_width' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_FEATURED_IMAGE_WIDTH)},
                    `ap_scs`.`option_page_featured_image_width`,
                    `ap_gcs`.`option_page_featured_image_width`
                )",
                'option_page_featured_image_height' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_FEATURED_IMAGE_HEIGHT)},
                    `ap_scs`.`option_page_featured_image_height`,
                    `ap_gcs`.`option_page_featured_image_height`
                )",
                'option_page_product_image_width' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_PRODUCT_IMAGE_WIDTH)},
                    `ap_scs`.`option_page_product_image_width`,
                    `ap_gcs`.`option_page_product_image_width`
                )",
                'option_page_product_image_height' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_PRODUCT_IMAGE_HEIGHT)},
                    `ap_scs`.`option_page_product_image_height`,
                    `ap_gcs`.`option_page_product_image_height`
                )",
                'option_page_show_product_image' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_SHOW_PRODUCT_IMAGE)},
                    `ap_scs`.`option_page_show_product_image`,
                    `ap_gcs`.`option_page_show_product_image`
                )",
                'option_page_sidebar_image_width' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_SIDEBAR_IMAGE_WIDTH)},
                    `ap_scs`.`option_page_sidebar_image_width`,
                    `ap_gcs`.`option_page_sidebar_image_width`
                )",
                'option_page_sidebar_image_height' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_SIDEBAR_IMAGE_HEIGHT)},
                    `ap_scs`.`option_page_sidebar_image_height`,
                    `ap_gcs`.`option_page_sidebar_image_height`
                )",
                'option_page_include_in_menu' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_INCLUDE_IN_MENU)},
                    `ap_scs`.`option_page_include_in_menu`,
                    `ap_gcs`.`option_page_include_in_menu`
                )",
                'option_page_is_active' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_IS_ACTIVE)},
                    `ap_scs`.`option_page_is_active`,
                    `ap_gcs`.`option_page_is_active`
                )",
                'option_page_is_featured' => "IF({$dbHelper->isCustom('ap_scs', Mana_AttributePage_Model_AttributePage_Abstract::DM_OPTION_PAGE_IS_FEATURED)},
                    `ap_scs`.`option_page_is_featured`,
                    `ap_gcs`.`option_page_is_featured`
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
            $aggregate->joinLeft($select, 'cX', $this->getTable('catalog/eav_attribute'), "`cX`.`attribute_id` = `aX`.`attribute_id`", $attrCount);
            $aggregate->joinLeft($select, 'lX', $this->getTable('eav/attribute_label'),
                $db->quoteInto("`lX`.`attribute_id` = `aX`.`attribute_id` AND `lX`.`store_id` = ?", $store->getId()), $attrCount);
            if ($this->coreHelper()->isManadevLayeredNavigationInstalled()) {
                $aggregate->joinLeft($select, 'fX', $this->getTable('mana_filters/filter2'),
                    "`fX`.`code` = `aX`.`attribute_code`", $attrCount);
                $aggregate->joinLeft($select, 'fsX', $this->getTable('mana_filters/filter2_store'),
                    $db->quoteInto("`fsX`.`global_id` = `fX`.`id` AND `fsX`.`store_id` = ?", $store->getId()), $attrCount);
            }
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

            // convert SELECT into UPDATE which acts as INSERT on DUPLICATE unique keys
            $selectSql = $select->__toString();
            $sql = $select->insertFromSelect($this->getTable('mana_attributepage/attributePage_store'), array_keys($fields));

            // run the statement
            $db->exec($sql);
        }
    }
}