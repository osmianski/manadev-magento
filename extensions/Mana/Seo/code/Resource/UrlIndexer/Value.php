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
class Mana_Seo_Resource_UrlIndexer_Value extends Mana_Seo_Resource_UrlIndexer {
    /**
     * @param Mana_Seo_Model_UrlIndexer $indexer
     * @param array $options
     */
    public function process($indexer, $options) {
        $db = $this->_getWriteAdapter();

        foreach ($this->_getSchemas() as $schema) {
            $urlKeyExpr = $this->_seoify("COALESCE(vs.value, vg.value)", $schema);
            $fields = array(
                'url_key' => new Zend_Db_Expr($urlKeyExpr),
                'type' => new Zend_Db_Expr("'mana_seo/url_value'"),
                'schema_id' => new Zend_Db_Expr($schema->getId()),
                'store_id' => new Zend_Db_Expr($schema->getStoreId()),
                'option_id' => new Zend_Db_Expr('`o`.`option_id`'),
                'unique_key' => new Zend_Db_Expr("CONCAT('{$schema->getId()}-', `o`.`option_id`, '-', $urlKeyExpr)"),
                'status' => new Zend_Db_Expr("'{$schema->getStatus()}'"),
            );

            /* @var $select Varien_Db_Select */
            $select = $this->_getFilterableAttributeSelect($db)
                ->joinInner(array('o' => $this->getTable('eav/attribute_option')), "`o`.`attribute_id` = `a`.`attribute_id`", null)
                ->joinLeft(array('vg' => $this->getTable('eav/attribute_option_value')), 'o.option_id = vg.option_id AND vg.store_id = 0', null)
                ->joinLeft(array('vs' => $this->getTable('eav/attribute_option_value')),
                    $db->quoteInto('o.option_id = vs.option_id AND vs.store_id = ?', $schema->getStoreId()),
                    null)
                ->columns($fields);

            // convert SELECT into UPDATE which acts as INSERT on DUPLICATE unique keys
            $sql = $select->insertFromSelect($this->getTargetTableName(), array_keys($fields));

            // run the statement
            $db->query($sql);
        }
    }
}