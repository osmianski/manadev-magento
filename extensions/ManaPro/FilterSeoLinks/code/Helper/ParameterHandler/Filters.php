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
        $db = $collection->getResource()->getReadConnection();
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

        $conditions = array();
        $internalParameterName = array();
        if (!$context->getExpectValue()) {
            $conditions[] = "(`main_table`.`is_parameter` = 1)";
            $internalParameterName[] = "`fg`.`code`";
            $collection->getSelect()
                ->joinLeft(array('fs' => $res->getTableName('mana_filters/filter2_store')),
                    "`fs`.`id` = `main_table`.`filter_id`", null)
                ->joinLeft(array('fg' => $res->getTableName('mana_filters/filter2')),
                    "`fg`.`id` = `fs`.`global_id`", null);
        }
        if ($context->getExpectValue() || $context->getSchema()->getIncludeFilterName() != Mana_Seo_Model_Schema::INCLUDE_ALWAYS) {
            $conditions[] = "(`main_table`.`is_value` = 1)";
            $collection->getSelect()
                ->joinLeft(array('v' => $res->getTableName('mana_filters/filter2_value_store')),
                    "`v`.`id` = `main_table`.`filter_value_id`", array(
                        'internal_value_name' => new Zend_Db_Expr("`v`.`option_id`")))
                ->joinLeft(array('vfs' => $res->getTableName('mana_filters/filter2_store')),
                    "`vfs`.`id` = `v`.`filter_id`", null)
                ->joinLeft(array('vfg' => $res->getTableName('mana_filters/filter2')),
                    "`vfg`.`id` = `vfs`.`global_id`" .
                    ($context->getExpectValue() ? $db->quoteInto(" AND `vfg`.`code` = ?", $context->getCurrentParameter()) : ''), null);
            $internalParameterName[] = "`vfg`.`code`";
        }
        if (count($conditions)) {
            $collection->getSelect()->where(implode(' OR ', $conditions));
        }
        if (count($internalParameterName)) {
            if (count($internalParameterName) > 1) {
                $internalParameterName = "COALESCE(".implode(', ', $internalParameterName).")";
            }
            else {
                $internalParameterName = $internalParameterName[0];
            }
            $collection->getSelect()->columns(array('internal_parameter_name' => new Zend_Db_Expr($internalParameterName)));
        }

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

    /**
     * @return $this
     */
    public function prepareForParameterEncoding() {
        return $this;
    }

    /**
     * @param array $parameters
     * @return array
     */
    public function getParameterPositions($parameters) {
        return array();
    }

    /**
     * @param string $parameter
     * @param string $value
     * @return bool
     */
    public function encodeParameter($parameter, $value) {
        return false;
    }

}