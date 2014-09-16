<?php
/** 
 * @category    Mana
 * @package     Mana_Page
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Page_Resource_UrlIndexer_SpecialFilter extends Mana_Seo_Resource_UrlIndexer {
    protected $_matchedEntities = array(
        Mana_Page_Model_Special::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE,
            Mage_Index_Model_Event::TYPE_DELETE,
        ),
    );

    /**
     * @param Mana_Seo_Model_UrlIndexer $indexer
     * @param Mage_Index_Model_Event $event
     */
    public function register($indexer, $event) {
        if ($event->getEntity() == Mana_Page_Model_Special::ENTITY) {
            $event->addNewData('special_filter_id', $event->getData('data_object')->getId());
        }
    }

    /**
     * @param Mana_Seo_Model_UrlIndexer $indexer
     * @param Mana_Seo_Model_Schema $schema
     * @param array $options
     */
    public function process($indexer, $schema, $options) {
        if (!isset($options['special_filter_id']) && !isset($options['store_id']) &&
            !isset($options['schema_global_id']) && !isset($options['schema_store_id']) && !$options['reindex_all'])
        {
            return;
        }

        $db = $this->_getWriteAdapter();

        /* @var $resource Mana_Page_Resource_Special */
        $resource = Mage::getResourceSingleton('mana_page/special');
        $data = $resource->getData($schema->getStoreId());

        // mark special filter parameter as obsolete
        $obsoleteCondition = "(`schema_id` = " . $schema->getId() . ") AND (`is_parameter` = 1) AND (`type` = '".
            Mana_Seo_Model_ParsedUrl::PARAMETER_SPECIAL."')";
        $this->makeAllRowsObsolete($options, $obsoleteCondition);

        // insert/update special filter parameter
        $urlKeyExpr = "'".$this->specialPageHelper()->getRequestVar(). "'";
        $fields = array(
            'url_key' => new Zend_Db_Expr($urlKeyExpr),
            'type' => new Zend_Db_Expr("'" . Mana_Seo_Model_ParsedUrl::PARAMETER_SPECIAL . "'"),
            'is_page' => new Zend_Db_Expr('0'),
            'is_parameter' => new Zend_Db_Expr('1'),
            'is_attribute_value' => new Zend_Db_Expr('0'),
            'is_category_value' => new Zend_Db_Expr('0'),
            'schema_id' => new Zend_Db_Expr($schema->getId()),
            'unique_key' => new Zend_Db_Expr($urlKeyExpr),
            'internal_name' => new Zend_Db_Expr($urlKeyExpr),
            'position' => new Zend_Db_Expr($this->specialPageHelper()->getSpecialFilterPosition()),
            'status' => new Zend_Db_Expr("'" . Mana_Seo_Model_Url::STATUS_ACTIVE . "'"),
            'description' => new Zend_Db_Expr("'{$this->specialPageHelper()->__('URL key for special filter parameter')}'"),
        );
        $sql = $this->insert($this->getTargetTableName(), $fields);
        $db->exec($sql);

        // delete all URL keys with non-existent special_filter_id
        $obsoleteCondition = "(`schema_id` = " . $schema->getId() . ") AND (`is_attribute_value` = 1) AND (`type` = '".
            Mana_Seo_Model_ParsedUrl::PARAMETER_SPECIAL."')";
        if (isset($options['special_filter_id'])) {
            $obsoleteCondition .= " AND `special_filter_id` = {$options['special_filter_id']}";
        }
        $deleteCondition = count($data)
            ? "`special_filter_id` NOT IN (" . implode(', ', array_keys($data)). ")"
            : '';
        if ($deleteCondition) {
            $deleteCondition .= " AND ";
        }
        $deleteCondition .= $obsoleteCondition;
        if ($deleteCondition) {
            $deleteCondition = " WHERE " . $deleteCondition;
        }

        $db->exec("DELETE FROM {$this->getTargetTableName()} $deleteCondition");

        // mark all special filter URL keys as obsolete
        $this->makeAllRowsObsolete($options, $obsoleteCondition);

        // insert/update all special filter URL keys
        foreach ($data as $id => $special) {
            if (!isset($options['special_filter_id']) || $options['special_filter_id'] == $id) {
                $urlKeyExpr = "'". $special['url_key']. "'";
                $fields = array(
                    'url_key' => new Zend_Db_Expr($urlKeyExpr),
                    'type' => new Zend_Db_Expr("'" . Mana_Seo_Model_ParsedUrl::PARAMETER_SPECIAL . "'"),
                    'include_filter_name' => new Zend_Db_Expr(
                        $this->specialPageHelper()->includeRequestVarInUrl() == Mana_Seo_Model_Source_IncludeInUrl::ALWAYS
                            ? "1"
                            : ($this->specialPageHelper()->includeRequestVarInUrl() == Mana_Seo_Model_Source_IncludeInUrl::NEVER
                                ? "0"
                                : "{$schema->getIncludeFilterName()}))"
                            )),
                    'is_page' => new Zend_Db_Expr('0'),
                    'is_parameter' => new Zend_Db_Expr('0'),
                    'is_attribute_value' => new Zend_Db_Expr('1'),
                    'is_category_value' => new Zend_Db_Expr('0'),
                    'schema_id' => new Zend_Db_Expr($schema->getId()),
                    'unique_key' => new Zend_Db_Expr("CONCAT('$id', '-', $urlKeyExpr)"),
                    'internal_name' => new Zend_Db_Expr("''"),
                    'position' => new Zend_Db_Expr($special['position']),
                    'status' => new Zend_Db_Expr("'" . Mana_Seo_Model_Url::STATUS_ACTIVE . "'"),
                    'special_filter_id' => new Zend_Db_Expr($id),
                    'description' => new Zend_Db_Expr("'{$this->specialPageHelper()->__('URL key for special filter %s', $special['title'])}'"),
                );
                $sql = $this->insert($this->getTargetTableName(), $fields);
                $db->exec($sql);
            }
        }
    }

    #region Dependencies

    /**
     * @return Mana_Page_Helper_Special
     */
    public function specialPageHelper() {
        return Mage::helper('mana_page/special');
    }

    #endregion
}