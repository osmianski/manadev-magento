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
class Mana_AttributePage_Resource_OptionPage_UrlIndexer extends Mana_Seo_Resource_AttributeUrlIndexer {
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
        Mage_Catalog_Model_Resource_Eav_Attribute::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE
        ),
        'mana_filters/filter2' => array(
            Mage_Index_Model_Event::TYPE_SAVE
        ),
        'mana_filters/filter2_store' => array(
            Mage_Index_Model_Event::TYPE_SAVE
        ),
        Mana_AttributePage_Model_AttributePage_GlobalCustomSettings::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE
        ),
        Mana_AttributePage_Model_AttributePage_StoreCustomSettings::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE,
            Mage_Index_Model_Event::TYPE_DELETE
        ),
        Mana_AttributePage_Model_OptionPage_GlobalCustomSettings::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE,
            Mage_Index_Model_Event::TYPE_DELETE
        ),
        Mana_AttributePage_Model_OptionPage_StoreCustomSettings::ENTITY => array(
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
        elseif ($event->getEntity() == Mage_Catalog_Model_Resource_Eav_Attribute::ENTITY) {
            $event->addNewData('attribute_id', $event->getData('data_object')->getId());
        }
        elseif ($event->getEntity() == 'mana_filters/filter2') {
            if ($attributeId = $this->getFilterResource()->getAttributeId($event->getData('data_object'))) {
                $event->addNewData('attribute_id', $attributeId);
            }
        }
        elseif ($event->getEntity() == 'mana_filters/filter2_store') {
            if ($attributeId = $this->getFilterStoreResource()->getAttributeId($event->getData('data_object'))) {
                $event->addNewData('attribute_id', $attributeId);
                $event->addNewData('store_id', $event->getData('data_object')->getData('store_id'));
            }
        }
        elseif ($event->getEntity() == Mana_AttributePage_Model_AttributePage_GlobalCustomSettings::ENTITY) {
            $event->addNewData('attribute_page_global_custom_settings_id', $event->getData('data_object')->getId());
        }
        elseif ($event->getEntity() == Mana_AttributePage_Model_AttributePage_StoreCustomSettings::ENTITY) {
            $event->addNewData('attribute_page_global_id', $event->getData('data_object')->getData('attribute_page_global_id'));
            $event->addNewData('store_id', $event->getData('data_object')->getData('store_id'));
        }
        elseif ($event->getEntity() == Mana_AttributePage_Model_OptionPage_GlobalCustomSettings::ENTITY) {
            if ($event->getType() == Mage_Index_Model_Event::TYPE_DELETE) {
                $event->addNewData('attribute_page_global_id', $event->getData('data_object')->getData('attribute_page_global_id'));
            }
            else {
                $event->addNewData('option_page_global_custom_settings_id', $event->getData('data_object')->getId());
            }
        }
        elseif ($event->getEntity() == Mana_AttributePage_Model_OptionPage_StoreCustomSettings::ENTITY) {
            $event->addNewData('option_page_global_id', $event->getData('data_object')->getData('option_page_global_id'));
            $event->addNewData('store_id', $event->getData('data_object')->getData('store_id'));
        }
    }

    /**
     * @param Mana_Seo_Model_UrlIndexer $indexer
     * @param Mana_Seo_Model_Schema $schema
     * @param array $options
     */
    public function process($indexer, $schema, $options) {
        if (!isset($options['attribute_id']) &&
            !isset($options['attribute_page_global_id']) &&
            !isset($options['attribute_page_global_custom_settings_id']) &&
            !isset($options['option_page_global_id']) &&
            !isset($options['option_page_global_custom_settings_id']) &&
            !isset($options['schema_global_id']) &&
            !isset($options['schema_store_id']) &&
            !isset($options['store_id']) &&
            empty($options['reindex_all'])
        )
        {
            return;
        }
        $db = $this->_getWriteAdapter();

        $fields = array(
            'url_key' => new Zend_Db_Expr($this->dbHelper()->makeNotEmpty("`op`.`url_key`")),
            'type' => new Zend_Db_Expr("'option_page'"),
            'is_page' => new Zend_Db_Expr('1'),
            'is_parameter' => new Zend_Db_Expr('0'),
            'is_attribute_value' => new Zend_Db_Expr('0'),
            'is_category_value' => new Zend_Db_Expr('0'),
            'schema_id' => new Zend_Db_Expr($schema->getId()),
            'attribute_page_id' => new Zend_Db_Expr('`ap`.`id`'),
            'option_page_id' => new Zend_Db_Expr('`op`.`id`'),
            'unique_key' => new Zend_Db_Expr("CONCAT(`op`.`id`, '-', `op`.`url_key`)"),
            'status' => new Zend_Db_Expr("IF(`op`.`is_active`, '" .
                Mana_Seo_Model_Url::STATUS_ACTIVE . "', '".
                Mana_Seo_Model_Url::STATUS_DISABLED . "')"),
            'description' => new Zend_Db_Expr(
                "CONCAT('{$this->seoHelper()->__('Option page')} \\'', " .
                "`op`.`title`, '\\' (ID ', `op`.`id`, ')')"),
        );

        /* @var $select Varien_Db_Select */
        $select = $db->select()
            ->distinct()
            ->from(array('op' => $this->getTable('mana_attributepage/optionPage_store')), null)
            ->joinInner(array('op_g' => $this->getTable('mana_attributepage/optionPage_global')),
                "`op_g`.`id` = `op`.`option_page_global_id`", null)
            ->joinInner(array('ap' => $this->getTable('mana_attributepage/attributePage_store')),
                $db->quoteInto("`ap`.`attribute_page_global_id` = `op_g`.`attribute_page_global_id` AND `ap`.`store_id` = ?", $schema->getStoreId()), null)
            ->columns($fields)
            ->where('`op`.`store_id` = ?', $schema->getStoreId());

        $obsoleteCondition = "(`schema_id` = " . $schema->getId() . ") AND (`is_page` = 1) AND (`type` = 'option_page')";

        $attributePageIds = false;
        $optionPageIds = false;
        if (isset($options['attribute_id'])) {
            $attributePageIds = $this->getAttributePageResource()->getIdsByAttributeId(
                $options['attribute_id']);
        }
        elseif (isset($options['attribute_page_global_id'])) {
            $attributePageIds = $this->getAttributePageResource()->getIdsByGlobalCustomSettingsId(
                $options['attribute_page_global_id']);
        }
        elseif (isset($options['attribute_page_global_custom_settings_id'])) {
            $attributePageIds = $this->getAttributePageResource()->getIdsByGlobalId(
                $options['attribute_page_global_custom_settings_id']);
        }
        elseif (isset($options['option_page_global_id'])) {
            $optionPageIds = $this->getOptionPageResource()->getIdsByGlobalCustomSettingsId(
                $options['option_page_global_id']);
        }
        elseif (isset($options['option_page_global_custom_settings_id'])) {
            $optionPageIds = $this->getOptionPageResource()->getIdsByGlobalId(
                $options['option_page_global_custom_settings_id']);
        }
        if ($attributePageIds !== false) {
            if (!count($attributePageIds)) {
                return;
            }
            $select->where('`ap`.`id` IN (?)', $attributePageIds);
            $obsoleteCondition .= ' AND (`attribute_page_id` IN (' . $db->quote($attributePageIds) . '))';
        }
        if ($optionPageIds !== false) {
            if (!count($optionPageIds)) {
                return;
            }
            $select->where('`op`.`id` IN (?)', $optionPageIds);
            $obsoleteCondition .= ' AND (`option_page_id` IN (' . $db->quote($optionPageIds) . '))';
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
    #region Dependencies
    /**
     * @return Mana_AttributePage_Resource_AttributePage_Store
     */
    public function getAttributePageResource() {
        return Mage::getResourceSingleton('mana_attributepage/attributePage_store');
    }

    /**
     * @return Mana_AttributePage_Resource_OptionPage_Store
     */
    public function getOptionPageResource() {
        return Mage::getResourceSingleton('mana_attributepage/optionPage_store');
    }
    #endregion
}