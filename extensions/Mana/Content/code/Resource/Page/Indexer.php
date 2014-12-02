<?php
/** 
 * @category    Mana
 * @package     Mana_Content
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Content_Resource_Page_Indexer extends Mana_Content_Resource_Page_Abstract {
    /**
     * Resource initialization
     */
    protected function _construct() {
        $this->_setResource('mana_content');
    }

    public function process($options) {
        $this->_calculateGlobalCustomSettings($options);
        $this->_calculateFinalGlobalSettings($options);
        $this->_calculateFinalStoreLevelSettings($options);
        $this->_calculateTags($options);
        $this->_copyReferencePages($options);
    }

    public function reindexAll() {
        $this->process(array('reindex_all' => true));
    }

    protected function _calculateFinalGlobalSettings($options) {
        if (isset($options['store_id']) ||
            !isset($options['page_global_custom_settings_id']) &&
            empty($options['reindex_all'])
        )
        {
            return;
        }

        $db = $this->_getWriteAdapter();
        $dbHelper = $this->dbHelper();

        $seoifyExpr = $this->coreHelper()->isManadevSeoInstalled()
            ? $this->seoHelper()->seoifyExpr("`p_gcs`.`title`")
            : $dbHelper->seoifyExpr("`p_gcs`.`title`");

        $fields = array(
            'page_global_custom_settings_id' => "`p_gcs`.`id`",
            'url_key' => "IF({$dbHelper->isCustom('p_gcs', Mana_Content_Model_Page_Abstract::DM_URL_KEY)},
                        `p_gcs`.`url_key`,
                        {$seoifyExpr}
                    )",
        );


        $select = $db->select();
        $select->from(array('p_gcs' => $this->getTable('mana_content/page_globalCustomSettings')), null);

        $select->columns($this->dbHelper()->wrapIntoZendDbExpr($fields));

        if (isset($options['page_global_custom_settings_id'])) {
            $select->where("`p_gcs`.`id` = ?", $options['page_global_custom_settings_id']);
        }

        // convert SELECT into UPDATE which acts as INSERT on DUPLICATE unique keys
        $selectSql = $select->__toString();
        $sql = $select->insertFromSelect($this->getTable('mana_content/page_global'), array_keys($fields));

        // run the statement
        $db->exec($sql);
    }

    protected function _calculateFinalStoreLevelSettings($options) {
        if (!isset($options['page_global_custom_settings_id']) &&
            !isset($options['page_global_id']) &&
            !isset($options['store_id']) &&
            empty($options['reindex_all'])
        )
        {
            return;
        }

        $db = $this->_getWriteAdapter();
        $dbHelper = $this->dbHelper();

        foreach (Mage::app()->getStores() as $store) {
            /* @var $store Mage_Core_Model_Store */
            if (isset($options['store_id']) && $store->getId() != $options['store_id']) {
                continue;
            }

            $fields = array(
                'page_global_id' => "`p_g`.`id`",
                'store_id' => $store->getId(),
                'page_store_custom_settings_id' => "`p_scs`.`id`",
                'is_active' => "IF({$dbHelper->isCustom('p_scs', Mana_Content_Model_Page_Abstract::DM_IS_ACTIVE)},
                    `p_scs`.`is_active`,
                    `p_gcs`.`is_active`
                )",
                'url_key' => "IF({$dbHelper->isCustom('p_scs', Mana_Content_Model_Page_Abstract::DM_URL_KEY)},
                    `p_scs`.`url_key`,
                    IF({$dbHelper->isCustom('p_gcs', Mana_Content_Model_Page_Abstract::DM_URL_KEY)},
                        `p_gcs`.`url_key`,
                        `p_g`.`url_key`
                    )
                )",
                'title' => "IF({$dbHelper->isCustom('p_scs', Mana_Content_Model_Page_Abstract::DM_TITLE)},
                    `p_scs`.`title`,
                    `p_gcs`.`title`
                )",
                'content' => "IF({$dbHelper->isCustom('p_scs', Mana_Content_Model_Page_Abstract::DM_CONTENT)},
                    `p_scs`.`content`,
                    `p_gcs`.`content`
                )",
                'page_layout' => "IF({$dbHelper->isCustom('p_scs', Mana_Content_Model_Page_Abstract::DM_PAGE_LAYOUT)},
                    `p_scs`.`page_layout`,
                    `p_gcs`.`page_layout`
                )",
                'layout_xml' => "IF({$dbHelper->isCustom('p_scs', Mana_Content_Model_Page_Abstract::DM_LAYOUT_XML)},
                    `p_scs`.`layout_xml`,
                    `p_gcs`.`layout_xml`
                )",
                'custom_design_active_from' => "IF({$dbHelper->isCustom('p_scs', Mana_Content_Model_Page_Abstract::DM_CUSTOM_DESIGN_ACTIVE_FROM)},
                    `p_scs`.`custom_design_active_from`,
                    `p_gcs`.`custom_design_active_from`
                )",
                'custom_design_active_to' => "IF({$dbHelper->isCustom('p_scs', Mana_Content_Model_Page_Abstract::DM_CUSTOM_DESIGN_ACTIVE_TO)},
                    `p_scs`.`custom_design_active_to`,
                    `p_gcs`.`custom_design_active_to`
                )",
                'custom_design' => "IF({$dbHelper->isCustom('p_scs', Mana_Content_Model_Page_Abstract::DM_CUSTOM_DESIGN)},
                    `p_scs`.`custom_design`,
                    `p_gcs`.`custom_design`
                )",
                'custom_layout_xml' => "IF({$dbHelper->isCustom('p_scs', Mana_Content_Model_Page_Abstract::DM_CUSTOM_LAYOUT_XML)},
                    `p_scs`.`custom_layout_xml`,
                    `p_gcs`.`custom_layout_xml`
                )",
                'meta_title' => "IF({$dbHelper->isCustom('p_scs', Mana_Content_Model_Page_Abstract::DM_META_TITLE)},
                    `p_gcs`.`title`,
                    `p_gcs`.`meta_title`
                )",
                'meta_keywords' => "IF({$dbHelper->isCustom('p_scs', Mana_Content_Model_Page_Abstract::DM_META_KEYWORDS)},
                    `p_scs`.`meta_keywords`,
                    IF({$dbHelper->isCustom('p_gcs', Mana_Content_Model_Page_Abstract::DM_META_KEYWORDS)},
                        `p_gcs`.`meta_keywords`,
                        `p_gcs`.`tags`
                    )
                )",
                'meta_description' => "IF({$dbHelper->isCustom('p_scs', Mana_Content_Model_Page_Abstract::DM_META_DESCRIPTION)},
                    `p_scs`.`meta_description`,
                    `p_gcs`.`meta_description`
                )",
                'position' => "IF({$dbHelper->isCustom('p_scs', Mana_Content_Model_Page_Abstract::DM_POSITION)},
                    `p_scs`.`position`,
                    `p_gcs`.`position`
                )",
                'level' => "IF({$dbHelper->isCustom('p_scs', Mana_Content_Model_Page_Abstract::DM_LEVEL)},
                    `p_scs`.`level`,
                    `p_gcs`.`level`
                )",
                'tags' => "IF({$dbHelper->isCustom('p_scs', Mana_Content_Model_Page_Abstract::DM_TAGS)},
                    `p_scs`.`tags`,
                    `p_gcs`.`tags`
                )",
            );

            $select = $db->select();
            $select
                ->from(array('p_g' => $this->getTable('mana_content/page_global')), null)
                ->joinInner(array('p_gcs' => $this->getTable('mana_content/page_globalCustomSettings')),
                    "`p_gcs`.`id` = `p_g`.`page_global_custom_settings_id`", null)
                ->joinLeft(array('p_scs' => $this->getTable('mana_content/page_storeCustomSettings')),
                    $db->quoteInto("`p_scs`.`page_global_id` = `p_g`.`id` AND `p_scs`.`store_id` = ?", $store->getId()), null);

            $select->columns($this->dbHelper()->wrapIntoZendDbExpr($fields));

            if (isset($options['page_global_custom_settings_id'])) {
                $select->where("`p_gcs`.`id` = ?", $options['page_global_custom_settings_id']);
            }

            if (isset($options['page_global_id'])) {
                $select->where("`p_g`.`id` = ?", $options['page_global_id']);
            }

            // convert SELECT into UPDATE which acts as INSERT on DUPLICATE unique keys
            $selectSql = $select->__toString();
            $sql = $select->insertFromSelect($this->getTable('mana_content/page_store'), array_keys($fields));

            // run the statement
            $db->exec($sql);
        }
    }

    protected function _calculateGlobalCustomSettings($options) {
        if (!isset($options['page_global_custom_settings_id']) &&
            !isset($options['page_global_id']) &&
            !isset($options['store_id']) &&
            empty($options['reindex_all'])
        ) {
            return;
        }

        $db = $this->_getWriteAdapter();
        $dbHelper = $this->dbHelper();


        $read = $this->_getReadAdapter();

        $sql = $read->select()
            ->from($this->getTable('mana_content/page_globalCustomSettings'), array(new Zend_Db_Expr("max(level)")));

        $maxLevel = (int)$read->fetchOne($sql);


        $ids = (isset($options['page_global_custom_settings_id']))
            ? Mage::getResourceModel("mana_content/page_globalCustomSettings")->getAllChildren($options['page_global_custom_settings_id'])
            : array();

        $fields = array(
            'id' => new Zend_Db_Expr("`mpgcs`.`id`"),
            'is_active' => new Zend_Db_Expr("IF({$dbHelper->isCustom('mpgcs', Mana_Content_Model_Page_Abstract::DM_IS_ACTIVE)},
                    `mpgcs`.`is_active`,
                    `mpgcs1`.`is_active`
                )"),
        );
        $table = $this->getTable("mana_content/page_globalCustomSettings");

        $setLevel['base'] = "UPDATE `{$table}` AS `mpgcs`
          SET `mpgcs`.`level` = 0
          WHERE `mpgcs`.`parent_id` IS NULL
          AND `mpgcs`.`level` <> 0";

        $setLevel['each'] = "UPDATE `{$table}` AS `mpgcs1`, `{$table}` AS `mpgcs`
          SET `mpgcs`.`level` = `mpgcs1`.`level` + 1
          WHERE `mpgcs1`.`id` = `mpgcs`.`parent_id`
          AND `mpgcs`.`level` <> `mpgcs1`.`level` + 1";

        $read = $this->_getReadAdapter();

        $query = $setLevel['base'];
        $query .= (isset($options['page_global_custom_settings_id']))
            ? " AND `mpgcs`.`id` IN (" . implode(",", $ids) . ")"
            : "";
        $db->exec($query);

        for($x = 0; $x <= $maxLevel; $x++) {
            $query = $setLevel['each'];
            $query .= " AND `mpgcs`.`level` = ". $x;
            $query .= (isset($options['page_global_custom_settings_id']))
                ? " AND `mpgcs`.`id` IN (" . implode(",", $ids) . ")"
                : "";
            $db->exec($query);
        }

        for ($x = 1; $x <= $maxLevel; $x++) {
            /* @var $select Varien_Db_Select */
            $select = $db->select()
                ->from(array('mpgcs' => $this->getTable("mana_content/page_globalCustomSettings")), null)
                ->joinInner(array('mpgcs1' => $this->getTable("mana_content/page_globalCustomSettings")), "`mpgcs1`.`id` = `mpgcs`.`parent_id`", array())
                ->columns($fields)
                ->where("`mpgcs`.`level` = ?", $x);

            if(count($ids)) {
                $select->where("`mpgcs`.`id` IN (". implode(",", $ids) .")");
            }

            $sql = $select->insertFromSelect($this->getTable('mana_content/page_globalCustomSettings'), array_keys($fields));

            // run the statement
            $db->exec($sql);
        }


    }

    protected function _calculateTags($options) {
        if (!isset($options['page_global_custom_settings_id']) &&
            !isset($options['page_global_id']) &&
            !isset($options['store_id']) &&
            empty($options['reindex_all'])
        ) {
            return;
        }

        $db = $this->_getWriteAdapter();
        $dbHelper = $this->dbHelper();
        $tags = array();
        $tagTable = $this->getTable('mana_content/page_tag');
        $tagRelationTable = $this->getTable('mana_content/page_tagRelation');
        $tagSummaryTable = $this->getTable('mana_content/page_tagSummary');

        if(isset($options['page_global_custom_settings_id'])) {
            $global_id = Mage::getModel('mana_content/page_global')->getGlobalId($options['page_global_custom_settings_id']);
            $sql = "DELETE FROM {$tagRelationTable} WHERE page_store_id IN (SELECT id FROM {$this->getTable('mana_content/page_store')} WHERE page_global_id = {$global_id})";
            $db->exec($sql);
            $select = $db->select()
                ->from($this->getTable('mana_content/page_globalCustomSettings'), array('tags'))
                ->where('id = ?', $options['page_global_custom_settings_id']);
            $tags = $this->contentProHelper()->tagStringToArray($db->fetchOne($select));
        } elseif(isset($options['page_global_id']) && isset($options['store_id'])) {
            $global_id = $options['page_global_id'];
            $store_id = $options['store_id'];
            $cond = sprintf("page_global_id = %d AND store_id = %d", $options['page_global_id'], $options['store_id']);
            $sql = "DELETE FROM {$tagRelationTable} WHERE page_store_id IN (SELECT id FROM {$this->getTable('mana_content/page_store')} WHERE page_global_id = {$global_id} AND store_id = {$store_id})";
            $db->exec($sql);
            $select = $db->select()
                ->from($this->getTable('mana_content/page_store'), array('tags'))
                ->where($cond);
            $tags = $this->contentHelper()->tagStringToArray($db->fetchOne($select));
        }
        else{
            $db->truncate($this->getTable('mana_content/page_tagRelation'));
            $select = $db->select()->from($this->getTable('mana_content/page_store'), array('tags'));
            $rows = $db->fetchAssoc($select);
            $tags = array();
            foreach($rows as $stringTag) {
                $tags = array_unique($tags, $this->contentHelper()->tagStringToArray($stringTag));
            }
        }

        foreach($tags as $tag) {
            $db->insertOnDuplicate($tagTable, array('name' => $tag));
        }
        array_walk($tags, function(&$value, $key) {
                $value = "'".$value."'";
            });
        if(count($tags) > 0) {
            $select = $db->select()->from($tagTable, array('id'))->where("name IN (". implode(",", $tags) .")");
            $tagIds = $db->fetchCol($select);
        } else {
            $tagIds = array();
        }

        foreach($tagIds as $tagId) {
            $sql = "INSERT INTO {$tagRelationTable}(page_store_id, page_tag_id)
        SELECT `mps`.id, {$tagId}
        FROM {$this->getTable('mana_content/page_store')} AS `mps`";
            if(isset($global_id)) {
                $sql .= "
            WHERE `mps`.page_global_id = {$global_id}
            ";
                if(isset($options['store_id'])) {
                    $sql .= "
                AND `mps`.`store_id` = {$options['store_id']}
                ";
                }
            }
            $db->exec($sql);
        }

        if(count($tagIds)) {
            $sql = "DELETE FROM {$tagSummaryTable}";
            $db->exec($sql);
            $sql = "
                INSERT INTO {$tagSummaryTable}(page_tag_id, store_id, popularity)
                SELECT `mptr`.`page_tag_id`, `mps`.`store_id`, COUNT(*) AS `popularity`
                FROM `{$tagRelationTable}` AS `mptr`
                INNER JOIN `{$this->getTable('mana_content/page_store')}` AS `mps` ON `mptr`.`page_store_id` = `mps`.`id`
                GROUP BY `mptr`.`page_tag_id`, `mps`.`store_id`
                ";
            $db->exec($sql);
            $sql = "DELETE FROM {$tagTable} WHERE id NOT IN (SELECT DISTINCT page_tag_id FROM {$tagSummaryTable})";
            $db->exec($sql);
        }
    }

    protected function _copyReferencePages($options) {
        if(isset($options['page_global_id'])) {
            $global_id = $options['page_global_id'];
        } else {
            $global_id = Mage::getModel('mana_content/page_globalCustomSettings')->getGlobalId($options['page_global_custom_settings_id']);
        }

        $rawFields = array(
            'id',
            'is_active',
            'url_key',
            'title',
            'content',
            'page_layout',
            'layout_xml',
            'custom_design_active_from',
            'custom_design_active_to',
            'custom_design',
            'custom_layout_xml',
            'meta_title',
            'meta_keywords',
            'meta_description',
            'tags'
        );
        $customTable = $this->getTable('mana_content/page_globalCustomSettings');
        $globalTable = $this->getTable('mana_content/page_global');
        $storeTable = $this->getTable('mana_content/page_store');

        $db = $this->_getWriteAdapter();
        $select =$db->select();
        $select->from(array('pg' => $globalTable), array())
            ->joinInner(array('pgcs' => $customTable), 'pg.page_global_custom_settings_id = pgcs.id', array())
            ->joinInner(array('pgo' => $globalTable), 'pgo.id = pgcs.reference_id', array())
            ->joinInner(array('pgcso' => $customTable), 'pgcso.id = pgo.page_global_custom_settings_id', array())
            ->where("pgo.id = ?", $global_id);

        $selects[$customTable] = $select;

        $select = $db->select();
        $select->from(array('ps' => $storeTable), array())
            ->joinInner(array('pg' => $globalTable), 'pg.id = ps.page_global_id', array())
            ->joinInner(array('pgcs' => $customTable), 'pgcs.id = pg.page_global_custom_settings_id', array())
            ->joinInner(array('pgo' => $globalTable), 'pgo.id = pgcs.reference_id', array())
            ->joinInner(array('pso' => $storeTable), 'pso.page_global_id = pgo.id', array())
            ->where('pgo.id = ?', $global_id);

        $selects[$storeTable] = $select;

        $tables = array(
            $customTable => 'pgcs',
            $storeTable => 'ps',
        );

        /** @var Varien_Db_Select $select */
        foreach($selects as $table => $select) {
            $fields = array();
            foreach ($rawFields as $key) {
                $alias = $tables[$table];
                if ($key != 'id') {
                    $alias .= 'o';
                }
                $fields[$key] = new Zend_Db_Expr("`{$alias}`.`{$key}`");
            }
            $select->columns($fields);
            $sql = $select->insertFromSelect($table, array_keys($fields));
            $db->exec($sql);
        }


    }
    #region Dependencies
    public function contentHelper() {
        return Mage::helper('mana_content');
    }

    public function contentProHelper() {
        return Mage::helper('manapro_content');
    }
    #endregion
}