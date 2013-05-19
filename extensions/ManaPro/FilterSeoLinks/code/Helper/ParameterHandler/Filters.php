<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterSeoLinks
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/* BASED ON SNIPPET: New Module/Helper/Data.php */
class ManaPro_FilterSeoLinks_Helper_ParameterHandler_Filters extends Mana_Seo_Helper_ParameterHandler {
    /**
     * @param Mana_Seo_Model_Context $context
     * @param Mana_Seo_Model_Url[] $activeParameterUrls
     * @param Mana_Seo_Model_Url[] $obsoleteParameterUrls
     * @return Mana_Seo_Helper_ParameterHandler
     */
    public function getParameterUrls($context, &$activeParameterUrls, &$obsoleteParameterUrls) {
        $activeParameterUrls = array();
        $obsoleteParameterUrls = array();

        /* @var $dbHelper Mana_Db_Helper_Data */
        $dbHelper = Mage::helper('mana_db');

        /* @var $res Mage_Core_Model_Resource */
        $res = Mage::getSingleton('core/resource');

        /* @var $collection Mana_Db_Resource_Entity_Collection */
        $collection = $dbHelper->getResourceModel('mana_seo/url_collection');
        $collection
            ->setStoreFilter($context->getStoreId())
            ->addFieldToFilter('schema_id', $context->getSchema()->getId())
            ->addFieldToFilter('url_key', array('in' => $context->getCandidates()))
            ->addFieldToFilter('status', array(
                'in' => array(
                    Mana_Seo_Model_Url::STATUS_ACTIVE,
                    Mana_Seo_Model_Url::STATUS_OBSOLETE
                )
            ));

        $conditions = array("(`main_table`.`is_parameter` = 1)");
        $internalParameterName = "`fg`.`code`";
        $collection->getSelect()
            ->joinLeft(array('fs' => $res->getTableName('mana_filters/filter2_store')),
                "`fs`.`id` = `main_table`.`filter_id`", null)
            ->joinLeft(array('fg' => $res->getTableName('mana_filters/filter2')),
                "`fg`.`id` = `fs`.`global_id`", null);
        if ($context->getSchema()->getIncludeFilterName() != Mana_Seo_Model_Schema::INCLUDE_ALWAYS) {
            $conditions[] = "(`main_table`.`is_value` = 1)";
            $collection->getSelect()
                ->joinLeft(array('v' => $res->getTableName('mana_filters/filter2_value_store')),
                    "`v`.`id` = `main_table`.`filter_value_id`", array(
                        'internal_value_name' => new Zend_Db_Expr("`v`.`option_id`")))
                ->joinLeft(array('vfs' => $res->getTableName('mana_filters/filter2_store')),
                    "`vfs`.`id` = `v`.`filter_id`", null)
                ->joinLeft(array('vfg' => $res->getTableName('mana_filters/filter2')),
                    "`vfg`.`id` = `vfs`.`global_id`", null);
            $internalParameterName = "COALESCE(`vfg`.`code`, $internalParameterName)";
        }
        $collection->getSelect()
            ->where(implode(' OR ', $conditions))
            ->columns(array('internal_parameter_name' => new Zend_Db_Expr($internalParameterName)));

        foreach ($collection as $parameterUrl) {
            /* @var $parameterUrl Mana_Seo_Model_Url */
            if ($parameterUrl->getStatus() == Mana_Seo_Model_Url::STATUS_ACTIVE) {
                $activeParameterUrls[] = $parameterUrl;
            }
            else {
                $obsoleteParameterUrls[] = $parameterUrl;
            }
        }

    }
}