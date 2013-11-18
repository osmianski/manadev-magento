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
class Mana_AttributePage_Resource_AttributePage_UrlIndexer extends Mana_Seo_Resource_AttributeUrlIndexer {
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

        $fields = array(
            'url_key' => new Zend_Db_Expr("`ap`.`url_key`"),
            'type' => new Zend_Db_Expr("'attribute_page'"),
            'is_page' => new Zend_Db_Expr('1'),
            'is_parameter' => new Zend_Db_Expr('0'),
            'is_attribute_value' => new Zend_Db_Expr('0'),
            'is_category_value' => new Zend_Db_Expr('0'),
            'schema_id' => new Zend_Db_Expr($schema->getId()),
            'attribute_page_id' => new Zend_Db_Expr('`ap`.`id`'),
            'unique_key' => new Zend_Db_Expr("CONCAT(`ap`.`id`, '-', `ap`.`url_key`)"),
            'status' => new Zend_Db_Expr("IF(`ap`.`is_active`, '" .
                Mana_Seo_Model_Url::STATUS_ACTIVE . "', '".
                Mana_Seo_Model_Url::STATUS_DISABLED . "')"),
            'description' => new Zend_Db_Expr(
                "CONCAT('{$this->seoHelper()->__('Attribute page')} \\'', " .
                "`ap`.`title`, '\\' (ID ', `ap`.`id`, ')')"),
        );

        /* @var $select Varien_Db_Select */
        $select = $db->select()
            ->distinct()
            ->from(array('ap' => $this->getTable('mana_attributepage/attributePage_store')), null)
            ->columns($fields)
            ->where('`ap`.`store_id` = ?', $schema->getStoreId());

        $obsoleteCondition = "(`schema_id` = " . $schema->getId() . ") AND (`is_page` = 1) AND (`type` = 'attribute_page')";

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