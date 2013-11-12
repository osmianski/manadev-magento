<?php
/** 
 * @category    Mana
 * @package     Mana_Seo
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_AttributePage_Resource_UrlIndexer_OptionPage extends Mana_Seo_Resource_AttributeUrlIndexer {
    protected $_matchedEntities = array(
        // inherited
        Mage_Catalog_Model_Resource_Eav_Attribute::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE
        ),
        'mana_filters/filter2' => array(
            Mage_Index_Model_Event::TYPE_SAVE
        ),
        'mana_filters/filter2_store' => array(
            Mage_Index_Model_Event::TYPE_SAVE
        ),

        // custom
        'mana_attributepage/page' => array(
            Mage_Index_Model_Event::TYPE_SAVE
        ),
        'mana_attributepage/page_store' => array(
            Mage_Index_Model_Event::TYPE_SAVE
        ),
        'mana_attributepage/option_page' => array(
            Mage_Index_Model_Event::TYPE_SAVE
        ),
        'mana_attributepage/option_page_store' => array(
            Mage_Index_Model_Event::TYPE_SAVE
        ),
    );

    /**
     * @param Mana_Seo_Model_UrlIndexer $indexer
     * @param Mage_Index_Model_Event $event
     */
    public function register($indexer, $event) {
        parent::register($indexer, $event);

        if ($event->getEntity() == 'mana_attributepage/page/global') {
            $event->addNewData('attribute_page_global_id', $event->getData('data_object')->getId());
        }
        if ($event->getEntity() == 'mana_attributepage/page/store') {
            $event->addNewData('attribute_page_store_id', $event->getData('data_object')->getId());
        }
        if ($event->getEntity() == 'mana_attributepage/option_page/global') {
            $event->addNewData('option_page_global_id', $event->getData('data_object')->getId());
        }
        if ($event->getEntity() == 'mana_attributepage/option_page/store') {
            $event->addNewData('option_page_store_id', $event->getData('data_object')->getId());
        }
    }

    /**
     * @param Mana_Seo_Model_UrlIndexer $indexer
     * @param Mana_Seo_Model_Schema $schema
     * @param array $options
     */
    public function process($indexer, $schema, $options) {
        if (!isset($options['attribute_page_global_id']) && !isset($options['attribute_page_store_id']) &&
            !isset($options['option_page_global_id']) && !isset($options['option_page_store_id']) &&
            !isset($options['attribute_id']) && !isset($options['store_id']) &&
            !isset($options['schema_global_id']) && !isset($options['schema_store_id']) && !$options['reindex_all']
        ) {
            return;
        }
        $db = $this->_getWriteAdapter();

        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper('mana_core');

        $urlKeyExpr = "";
        $concat = false;
        for ($i = 0; $i < Mana_AttributePage_Model_Page::MAX_ATTRIBUTE_COUNT; $i++) {
            $urlKeySubExpr = "";
            $subConcat = false;
            if ($i > 1) {
                $urlKeySubExpr .= "'{$schema->getParamSeparator()}'";
            }
            $includeFilterNameExpr = new Zend_Db_Expr($core->isManadevSeoLayeredNavigationInstalled()
                ? "IF(`f_$i`.include_in_url = '" . Mana_Seo_Model_Source_IncludeInUrl::ALWAYS . "', 1, " .
                "IF(`f_$i`.include_in_url = '" . Mana_Seo_Model_Source_IncludeInUrl::NEVER . "', 0, " .
                "{$schema->getIncludeFilterName()}))"
                : $schema->getIncludeFilterName());
            $filterNameExpr = $schema->getUseFilterLabels()
                ? ($core->isManadevLayeredNavigationInstalled()
                    ? $this->_seoify("`f_$i`.`name`", $schema)
                    : $this->_seoify("COALESCE(`l_$i`.`value`, `a_$i`.`frontend_label`)", $schema)
                )
                : "REPLACE(LOWER(`a_$i`.`attribute_code`), '_', '-')";
            if ($urlKeySubExpr) {
                $urlKeySubExpr .= ", ";
                $subConcat = true;
            }
            $urlKeySubExpr .= "IF($includeFilterNameExpr, CONCAT($filterNameExpr, '{$schema->getFirstValueSeparator()}'), '')";

            $optionNameExpr = $this->_seoify("COALESCE(vs_$i.value, vg_$i.value)", $schema);
            if ($urlKeySubExpr) {
                $urlKeySubExpr .= ", ";
                $subConcat = true;
            }
            $urlKeySubExpr .= $optionNameExpr;

            if ($urlKeyExpr) {
                $urlKeyExpr .= ", ";
                $concat = true;
            }
            if ($subConcat) {
                $urlKeySubExpr = "CONCAT($urlKeySubExpr)";
            }
            $urlKeyExpr .= "IF(`op`.`option_id_$i` IS NULL, '', $urlKeySubExpr)";

        }
        if ($concat) {
            $urlKeyExpr = "CONCAT($urlKeyExpr)";
        }

        $fields = array(
            'url_key' => new Zend_Db_Expr($urlKeyExpr),
            'type' => new Zend_Db_Expr("'option_page'"),
            'is_page' => new Zend_Db_Expr('1'),
            'is_parameter' => new Zend_Db_Expr('0'),
            'is_attribute_value' => new Zend_Db_Expr('0'),
            'is_category_value' => new Zend_Db_Expr('0'),
            'schema_id' => new Zend_Db_Expr($schema->getId()),
            'option_page_id' => new Zend_Db_Expr('`op`.`id`'),
            'option_page_store_id' => new Zend_Db_Expr('`op`.`primary_id`'),
            'option_page_global_id' => new Zend_Db_Expr('`op`.`primary_global_id`'),
            'attribute_page_id' => new Zend_Db_Expr('`ap`.`id`'),
            'attribute_page_store_id' => new Zend_Db_Expr('`ap`.`primary_id`'),
            'attribute_page_global_id' => new Zend_Db_Expr('`ap`.`primary_global_id`'),
            'unique_key' => new Zend_Db_Expr("CONCAT(`op`.`id`, '-', $urlKeyExpr)"),
            'status' => new Zend_Db_Expr("IF(`op`.`is_active`, '" .
                Mana_Seo_Model_Url::STATUS_ACTIVE . "', '".
                Mana_Seo_Model_Url::STATUS_DISABLED . "')"),
            'description' => new Zend_Db_Expr(
                "CONCAT('{$this->seoHelper()->__('Attribute Option page')} \\'', " .
                "`op`.`title`, '\\' (ID ', `op`.`id`, ')')"),
        );
        for ($i = 0; $i < Mana_AttributePage_Model_Page::MAX_ATTRIBUTE_COUNT; $i++) {
            $urlKeyField = $i == 0 ? 'attribute_id' : "attribute_id_$i";
            $fields[$urlKeyField] = new Zend_Db_Expr("`ap`.`attribute_id_$i`");
        }


        /* @var $select Varien_Db_Select */
        $select = $db->select()
            ->distinct()
            ->from(array('op' => $this->getTable('mana_attributepage/option_page_store_flat')), null)
            ->joinInner(array('ap' => $this->getTable('mana_attributepage/page_store_flat')), "`ap`.`id` = `op`.`attribute_page_id`", null)
            ->columns($fields)
            ->where('`op`.`store_id` = ?', $schema->getStoreId());
        for ($i = 0; $i < Mana_AttributePage_Model_Page::MAX_ATTRIBUTE_COUNT; $i++) {
            $select
                ->joinLeft(array("a_$i" => $this->getTable('eav/attribute')), "`a_$i`.`attribute_id` = `ap`.`attribute_id_$i`", null)
                ->joinLeft(array("vg_$i" => $this->getTable('eav/attribute_option_value')), "op.option_id_$i = vg_$i.option_id AND vg_$i.store_id = 0", null)
                ->joinLeft(array("vs_$i" => $this->getTable('eav/attribute_option_value')),
                    $db->quoteInto("op.option_id_$i = vs_$i.option_id AND vs_$i.store_id = ?", $schema->getStoreId()),
                    null)
                ->joinLeft(array("l_$i" => $this->getTable('eav/attribute_label')),
                    $db->quoteInto("`l_$i`.`attribute_id` = `a_$i`.`attribute_id` AND `l_$i`.`store_id` = ?", $schema->getStoreId()),
                    null);
            if ($core->isManadevLayeredNavigationInstalled()) {
                $select
                    ->joinInner(array("g_$i" => $this->getTable('mana_filters/filter2')), "`g_$i`.`code` = `a_$i`.`attribute_code`", null)
                    ->joinInner(array("f_$i" => $this->getTable('mana_filters/filter2_store')),
                        $db->quoteInto("`f_$i`.`global_id` = `g_$i`.`id` AND `f_$i`.`store_id` = ?", $schema->getStoreId()),
                        null);
            }
        }

        $obsoleteCondition = "(`schema_id` = " . $schema->getId() . ") AND (`is_page` = 1) AND (`type` = 'option_page')";
        if (isset($options['option_page_store_id'])) {
            $select->where('`op`.`primary_id` = ?', $options['option_page_store_id']);
            $obsoleteCondition .= ' AND (`option_page_store_id` = ' . $options['option_page_store_id'] . ')';
        }
        if (isset($options['option_page_global_id'])) {
            $select->where('`op`.`primary_global_id` = ?', $options['option_page_global_id']);
            $obsoleteCondition .= ' AND (`option_page_global_id` = ' . $options['option_page_store_id'] . ')';
        }
        if (isset($options['attribute_page_store_id'])) {
            $select->where('`ap`.`primary_id` = ?', $options['attribute_page_store_id']);
            $obsoleteCondition .= ' AND (`attribute_page_store_id` = ' . $options['option_page_store_id'] . ')';
        }
        if (isset($options['attribute_page_global_id'])) {
            $select->where('`ap`.`primary_global_id` = ?', $options['attribute_page_global_id']);
            $obsoleteCondition .= ' AND (`attribute_page_global_id` = ' . $options['option_page_store_id'] . ')';
        }
        if (isset($options['attribute_id'])) {
            $filterCondition = array();
            $obsoleteSubCondition = array();
            for ($i = 0; $i < Mana_AttributePage_Model_Page::MAX_ATTRIBUTE_COUNT; $i++) {
                $urlKeyField = $i == 0 ? 'attribute_id' : "attribute_id_$i";
                $filterCondition[] = $db->quoteInto("(`ap`.`attribute_id_$i` = ?)", $options['attribute_id']);
                $obsoleteSubCondition[] = $db->quoteInto("(`$urlKeyField` = ?)", $options['attribute_id']);
            }
            $select->where(implode(' OR ', $filterCondition));
            $obsoleteCondition .= ' AND ('.implode(' OR ', $obsoleteSubCondition).')';
        }

        // convert SELECT into UPDATE which acts as INSERT on DUPLICATE unique keys
        $this->logger()->logUrlIndexer('-----------------------------');
        $this->logger()->logUrlIndexer(get_class($this));
        $this->logger()->logUrlIndexer($select->__toString());
        $this->logger()->logUrlIndexer($schema->getId());
        $this->logger()->logUrlIndexer($obsoleteCondition);
        $this->logger()->logUrlIndexer(json_encode($options));
        $sql = $select->insertFromSelect($this->getTargetTableName(), array_keys($fields));

        // run the statement
        $this->makeAllRowsObsolete($options, $obsoleteCondition);
        $db->exec($sql);
    }
}