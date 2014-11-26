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
class Mana_Content_Resource_Page_UrlIndexer extends Mana_Seo_Resource_UrlIndexer {
    protected $_matchedEntities = array(
        Mage_Core_Model_Store::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE,
        ),
        'mana_seo/schema/global' => array(
            Mage_Index_Model_Event::TYPE_SAVE,
        ),
        'mana_seo/schema/store' => array(
            Mage_Index_Model_Event::TYPE_SAVE,
        ),
        Mana_Content_Model_Page_GlobalCustomSettings::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE
        ),
        Mana_Content_Model_Page_StoreCustomSettings::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE,
            Mage_Index_Model_Event::TYPE_DELETE
        ),
    );

    /**
     * @param Mana_Seo_Model_UrlIndexer $indexer
     * @param Mage_Index_Model_Event $event
     */
    public function register($indexer, $event) {
        if ($event->getEntity() == Mage_Core_Model_Store::ENTITY) {
            if ($event->getData('data_object')->isObjectNew()) {
                $event->addNewData('store_id', $event->getData('data_object')->getId());
            }
        }
        elseif ($event->getEntity() == 'mana_seo/schema/global') {
                $event->addNewData('reindex_all', true);
        }
        elseif ($event->getEntity() == 'mana_seo/schema/store') {
            $event->addNewData('reindex_all', true);
            $event->addNewData('store_id', $event->getData('data_object')->getData('store_id'));
        }
        elseif ($event->getEntity() == Mana_Content_Model_Page_GlobalCustomSettings::ENTITY) {
            $event->addNewData('page_global_custom_settings_id', $event->getData('data_object')->getId());
        }
        elseif ($event->getEntity() == Mana_Content_Model_Page_StoreCustomSettings::ENTITY) {
            $event->addNewData('page_global_id', $event->getData('data_object')->getData('page_global_id'));
            $event->addNewData('store_id', $event->getData('data_object')->getData('store_id'));
        }
    }

    /**
     * @param Mana_Seo_Model_UrlIndexer $indexer
     * @param Mana_Seo_Model_Schema $schema
     * @param array $options
     */
    public function process($indexer, $schema, $options) {
        if (!isset($options['page_global_id']) &&
            !isset($options['page_global_custom_settings_id']) &&
            !isset($options['store_id']) &&
            empty($options['reindex_all'])
        )
        {
            return;
        }

        $db = $this->_getWriteAdapter();
        $read = $this->_getReadAdapter();
        $table = $this->getTable("mana_content/page_globalCustomSettings");

        $sql = $read->select()
            ->from($this->getTable('mana_content/page_globalCustomSettings'), array(new Zend_Db_Expr("max(level)")));

        $maxLevel = (int)$read->fetchOne($sql);

        $ids = (isset($options['page_global_custom_settings_id']))
            ? Mage::getResourceModel("mana_content/page_globalCustomSettings")->getAllChildren($options['page_global_custom_settings_id'])
            : array();

        $fields = array(
            'url_key' => new Zend_Db_Expr("`cp`.`url_key`"),
            'type' => new Zend_Db_Expr("'book_page'"),
            'is_page' => new Zend_Db_Expr('1'),
            'is_parameter' => new Zend_Db_Expr('0'),
            'is_attribute_value' => new Zend_Db_Expr('0'),
            'is_category_value' => new Zend_Db_Expr('0'),
            'schema_id' => new Zend_Db_Expr($schema->getId()),
            'book_page_id' => new Zend_Db_Expr('`cp`.`id`'),
            'unique_key' => new Zend_Db_Expr("CONCAT(`cp`.`id`, '-', `cp`.`url_key`)"),
            'status' => new Zend_Db_Expr("IF(`cp`.`is_active`, '" .
                    Mana_Seo_Model_Url::STATUS_ACTIVE . "', '" .
                    Mana_Seo_Model_Url::STATUS_DISABLED . "')"),
            'description' => new Zend_Db_Expr(
                    "CONCAT('{$this->seoHelper()->__('Book page')} \\'', " .
                    "`cp`.`title`, '\\' (ID ', `cp`.`id`, ')')"),
        );

        /* @var $select Varien_Db_Select */
        $select = $db->select()
            ->distinct()
            ->from(array('cp' => $this->getTable('mana_content/page_store')), null)
            ->joinInner(array('mpg' => $this->getTable("mana_content/page_global")), "`mpg`.`id` = `cp`.`page_global_id`", array())
            ->joinInner(array('mpgcs' => $this->getTable("mana_content/page_globalCustomSettings")), "`mpgcs`.`id` = `mpg`.`page_global_custom_settings_id`", array())
            ->columns($fields)
            ->where('`cp`.`store_id` = ?', $schema->getStoreId())
            ->where('`mpgcs`.`parent_id` IS NULL');

        $obsoleteCondition = "(`msu`.`schema_id` = " . $schema->getId() . ") AND (`msu`.`is_page` = 1) AND (`msu`.`type` = 'book_page')";

        if(count($ids)) {
            $select->where('`mpgcs`.`id` IN (?)', implode(",", $ids));
            $obsoleteCondition .= " AND `mpgcs`.`id` IN (". implode(",", $ids) .")";
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
        for ($x = 1; $x <= $maxLevel; $x++) {
            $customFields = array(
                'url_key' => new Zend_Db_Expr("CONCAT(`msu`.`url_key`, '/', `cp`.`url_key`)"),
            );

            $select = $read->select()
                ->distinct()
                ->from(array('cp' => $this->getTable('mana_content/page_store')), null)
                ->joinInner(array('mpg' => $this->getTable("mana_content/page_global")), "`cp`.`page_global_id` = `mpg`.`id`", array())
                ->joinInner(array('mpgcs' => $table), "`mpgcs`.`id` = `mpg`.`page_global_custom_settings_id`", array())
                ->joinInner(array('mpgcs1' => $table), "`mpgcs`.`parent_id` = `mpgcs1`.`id`", array())
                ->joinInner(array('mpg1' => $this->getTable("mana_content/page_global")), "`mpg1`.`page_global_custom_settings_id` = `mpgcs1`.`id`", array())
                ->joinInner(array('mps1' => $this->getTable("mana_content/page_store")), "`mps1`.`page_global_id` = `mpg1`.`id`", array())
                ->joinInner(array('msu' => $this->getTargetTableName()), "`mps1`.`id` = `msu`.`book_page_id`", array())
                ->columns(array_merge($fields, $customFields))
                ->where('`cp`.`store_id` = ?', $schema->getStoreId())
                ->where("`mpgcs`.`level` = ?", $x);

            if(count($ids)) {
                $select->where("`mpgcs`.`id` IN (". implode(",", $ids) .")");
            }
            $sql = $select->insertFromSelect($this->getTargetTableName(), array_keys($fields));
            $db->exec($sql);
        }
    }

    public function makeAllRowsObsolete($options, $condition) {
        $db = $this->_getWriteAdapter();

        $db->query(
            "UPDATE
                `{$this->getTable("mana_seo/url")}` AS msu,
                `{$this->getTable("mana_content/page_globalCustomSettings")}` AS `mpgcs`,
                `{$this->getTable("mana_content/page_global")}` AS `mpg`,
                `{$this->getTable("mana_content/page_store")}` AS `mps`
            SET
              `msu`.`status` = 'obsolete'
            WHERE
              `mpg`.`page_global_custom_settings_id` = `mpgcs`.`id` AND
              `mps`.`page_global_id` = `mpg`.`id` AND
              `msu`.`book_page_id` = `mps`.`id` AND
              `msu`.`status` = 'active' AND
              {$condition}"
        );
    }

    protected function _extendRecursively($element, $book) {
        $id = $book->getId();
        $xmlId = 'c_' . $id;
        $route = "mana_content/book/view";
        $element->items->$xmlId->url = Mage::getUrl($route, array('_use_rewrite' => true, 'id' => $id));
        $element->items->$xmlId->route = $route;
        $element->items->$xmlId->label = $book->getTitle();
        $book->loadChildPages();


        foreach ($book->getChildPages() as $record) {
            $this->_extendRecursively($element->items->$xmlId, $record);
        }
    }
}