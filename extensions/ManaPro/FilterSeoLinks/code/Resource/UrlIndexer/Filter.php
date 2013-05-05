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
class ManaPro_FilterSeoLinks_Resource_UrlIndexer_Filter extends Mana_Seo_Resource_UrlIndexer {
    /**
     * @param Mana_Seo_Model_UrlIndexer $indexer
     * @param array $options
     */
    public function process($indexer, $options) {
        $db = $this->_getWriteAdapter();

        foreach ($this->_getSchemas() as $schema) {
            $fields = array(
                'url_key' => new Zend_Db_Expr($this->_seoify(
                    Mage::getStoreConfigFlag('mana_filters/seo/use_label') ? '`f`.`name`' : '`g`.`code`',
                    $schema)),
                'type' => new Zend_Db_Expr("'manapro_filterseolinks/url_filter'"),
                'is_page' => new Zend_Db_Expr(0),
                'supports_parameters' => new Zend_Db_Expr(0),
                'is_parameter' => new Zend_Db_Expr(1),
                'is_value' => new Zend_Db_Expr(0),
                'schema_id' => new Zend_Db_Expr($schema->getId()),
                'store_id' => new Zend_Db_Expr('`f`.`store_id`'),
                'filter_id' => new Zend_Db_Expr('`f`.`id`'),
                'unique_key' => new Zend_Db_Expr("CONCAT('{$schema->getId()}-', `f`.`id`)"),
                'status' => new Zend_Db_Expr("'{$schema->getStatus()}'"),
            );

            /* @var $select Varien_Db_Select */
            $select = $db->select()
                ->from(array('f' => $this->getTable('mana_filters/filter2_store')), null)
                ->joinInner(array('g' => $this->getTable('mana_filters/filter2')), '`g`.`id` = `f`.`global_id`', null)
                ->columns($fields);

            // convert SELECT into UPDATE which acts as INSERT on DUPLICATE unique keys
            $sql = $select->insertFromSelect($this->getTargetTableName(), array_keys($fields));

            // run the statement
            $db->query($sql);
        }
    }
}