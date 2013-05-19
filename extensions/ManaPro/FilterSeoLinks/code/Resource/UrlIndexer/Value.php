<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterSeoLinks
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_FilterSeoLinks_Resource_UrlIndexer_Value extends Mana_Seo_Resource_UrlIndexer {
    /**
     * @param Mana_Seo_Model_UrlIndexer $indexer
     * @param array $options
     */
    public function process($indexer, $options) {
        $db = $this->_getWriteAdapter();

        foreach ($this->_getSchemas() as $schema) {
            $fields = array(
                'url_key' => new Zend_Db_Expr($this->_seoify("COALESCE(vs.value, vg.value)", $schema)),
                'type' => new Zend_Db_Expr("'manapro_filterseolinks/url_value'"),
                'is_page' => new Zend_Db_Expr(0),
                'supports_parameters' => new Zend_Db_Expr(0),
                'is_parameter' => new Zend_Db_Expr(0),
                'is_value' => new Zend_Db_Expr(1),
                'schema_id' => new Zend_Db_Expr($schema->getId()),
                'store_id' => new Zend_Db_Expr('`v`.`store_id`'),
                'filter_value_id' => new Zend_Db_Expr('`v`.`id`'),
                'unique_key' => new Zend_Db_Expr("CONCAT('{$schema->getId()}-', `v`.`id`)"),
                'status' => new Zend_Db_Expr("'{$schema->getStatus()}'"),
            );

            /* @var $select Varien_Db_Select */
            $select = $db->select()
                ->from(array('v' => $this->getTable('mana_filters/filter2_value_store')), null)
                ->joinLeft(array('vg' => $this->getTable('eav/attribute_option_value')), 'v.option_id = vg.option_id AND vg.store_id = 0', null)
                ->joinLeft(array('vs' => $this->getTable('eav/attribute_option_value')), 'v.option_id = vs.option_id AND vs.store_id = v.store_id', null)
                ->columns($fields);

            // convert SELECT into UPDATE which acts as INSERT on DUPLICATE unique keys
            $sql = $select->insertFromSelect($this->getTargetTableName(), array_keys($fields));

            // run the statement
            $db->query($sql);
        }
    }
}