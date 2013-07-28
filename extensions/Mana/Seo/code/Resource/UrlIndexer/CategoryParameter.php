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
class Mana_Seo_Resource_UrlIndexer_CategoryParameter extends Mana_Seo_Resource_UrlIndexer {
    protected $_matchedEntities = array(
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
    public function register(/** @noinspection PhpUnusedParameterInspection */$indexer, $event) {
        $db = $this->_getReadAdapter();

        if ($event->getEntity() == 'mana_filters/filter2') {
            if ($event->getData('data_object')->getType() == 'category') {
                $event->addNewData('process_category_filter', true);
            }
        }
        elseif ($event->getEntity() == 'mana_filters/filter2_store') {
            $attributeType = $db->fetchOne($db->select()
                ->from(array('f' => $this->getTable('mana_filters/filter2')), 'type')
                ->where('f.id = ?', $event->getData('data_object')->getGlobalId()));
            if ($attributeType == 'category') {
                $event->addNewData('process_category_filter', true);
            }
        }
    }

    /**
     * @param Mana_Seo_Model_UrlIndexer $indexer
     * @param Mana_Seo_Model_Schema $schema
     * @param array $options
     */
    public function process($indexer, $schema, $options) {
        if (!isset($options['store_id']) && !isset($options['process_category_filter']) &&
            !isset($options['schema_global_id']) && !isset($options['schema_store_id']) && !$options['reindex_all']
        ) {
            return;
        }

        $db = $this->_getWriteAdapter();

        Mage::app()->getLocale()->emulate($schema->getStoreId());
        $defaultLabel = Mage::helper('catalog')->__('Category');
        Mage::app()->getLocale()->revert();

        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper('mana_core');

        $urlKeyExpr = $core->isManadevLayeredNavigationInstalled()
            ? ($schema->getUseFilterLabels() ? $this->_seoify('`f`.`name`', $schema) : "'category'")
            : ($schema->getUseFilterLabels() ? $this->_seoify($defaultLabel, $schema) : "'category'");
        $fields = array(
            'url_key' => new Zend_Db_Expr($urlKeyExpr),
            'internal_name' => new Zend_Db_Expr("'cat'"),
            'position' => new Zend_Db_Expr($core->isManadevLayeredNavigationInstalled() ? '`f`.`position`': "-1"),
            'type' => new Zend_Db_Expr("'" . Mana_Seo_Model_ParsedUrl::PARAMETER_CATEGORY . "'"),
            'is_page' => new Zend_Db_Expr('0'),
            'is_parameter' => new Zend_Db_Expr('1'),
            'is_attribute_value' => new Zend_Db_Expr('0'),
            'is_category_value' => new Zend_Db_Expr('0'),
            'schema_id' => new Zend_Db_Expr($schema->getId()),
            'unique_key' => new Zend_Db_Expr($urlKeyExpr),
            'status' => new Zend_Db_Expr("'". Mana_Seo_Model_Url::STATUS_ACTIVE."'"),
        );

        $obsoleteCondition = "(`schema_id` = " . $schema->getId() . ") AND (`is_parameter` = 1) AND (`type` = '" .
            Mana_Seo_Model_ParsedUrl::PARAMETER_CATEGORY . "')";

        if ($core->isManadevLayeredNavigationInstalled()) {
            /* @var $select Varien_Db_Select */
            $select = $db->select()
                ->from(array('g' => $this->getTable('mana_filters/filter2')), null)
                ->joinInner(array('f' => $this->getTable('mana_filters/filter2_store')),
                    $db->quoteInto("`f`.`global_id` = `g`.`id` AND `f`.`store_id` = ?", $schema->getStoreId()),
                    null)
                ->where("`g`.`type` = ?", 'category');

            $select->columns($fields);

            // convert SELECT into UPDATE which acts as INSERT on DUPLICATE unique keys
            Mage::log('-----------------------------', Zend_log::DEBUG, 'm_url.log');
            Mage::log(get_class($this), Zend_log::DEBUG, 'm_url.log');
            Mage::log($select->__toString(), Zend_log::DEBUG, 'm_url.log');
            Mage::log($schema->getId(), Zend_log::DEBUG, 'm_url.log');
            Mage::log($obsoleteCondition, Zend_log::DEBUG, 'm_url.log');
            Mage::log(json_encode($options), Zend_log::DEBUG, 'm_url.log');
            $sql = $select->insertFromSelect($this->getTargetTableName(), array_keys($fields));
        }
        else {
            $sql = $this->insert($this->getTargetTableName(), $fields);
        }

        // run the statement
        $this->makeAllRowsObsolete($options, $obsoleteCondition);
        $db->raw_query($sql);
    }
}