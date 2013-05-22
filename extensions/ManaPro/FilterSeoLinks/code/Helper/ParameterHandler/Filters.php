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

        /* @var $collection Mana_Seo_Resource_Url_Collection */
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

        $types = array();
        $internalParameterName = array();
        if (!$context->getExpectValue()) {
            $types[] = 'parameter';
            $internalParameterName[] = "`a`.`attribute_code`";
            $collection->getSelect()
                ->joinLeft(array('a' => $res->getTableName('eav/attribute')),
                    "`a`.`attribute_id` = `main_table`.`attribute_id`", null);
        }
        if ($context->getExpectValue() || $context->getSchema()->getIncludeFilterName() != Mana_Seo_Model_Schema::INCLUDE_ALWAYS) {
            $types[] = 'value';
            $collection->getSelect()
                ->joinLeft(array('o' => $res->getTableName('eav/attribute_option')),
                    "`o`.`option_id` = `main_table`.`option_id`", null)
                ->joinLeft(array('oa' => $res->getTableName('eav/attribute')),
                    "`oa`.`attribute_id` = `o`.`attribute_id`".
                    ($context->getExpectValue() ? $db->quoteInto(" AND `oa` . `code` = ?", $context->getCurrentParameter()) : ''), null);
            $internalParameterName[] = "`oa`.`attribute_code`";
        }
        $collection->addTypeFilter($types);
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