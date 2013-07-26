<?php
/**
 * @category    Mana
 * @package     Mana_Db
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */

class Mana_Db_Resource_Entity_Indexer extends Mage_Core_Model_Mysql4_Abstract {
    /**
     * Resource initialization
     */
    protected function _construct() {
        $this->_setResource('core');
    }

    /**
     * @param Mana_Db_Model_Entity_Indexer $indexer provides access to process record and indexer setup
     * @param Varien_Simplexml_Element $target setup in config.xml
     * @param Varien_Simplexml_Element $scope setup in m_db.xml
     * @param array $options on which records to run
     * @return void
     */
    public function flattenScope($indexer, $target, $scope, $options) {
        /** @noinspection PhpUndefinedFieldInspection */
        $targetEntity = ((string)$target->entity) . '/' . $scope->getName();

        if (isset($options['entity_filters']) && !isset($options['entity_filters'][$targetEntity])) {
            return;
        }
        $options = array_merge(array(
            'provide_field_details_in_exceptions' => true,
        ), $options);
        if (isset($options['entity_filters']) && !isset($options['entity_filter_formula'])) {
            $options['entity_filter_formula'] = '{{= ' . $options['entity_filters'][$targetEntity] . '}}';
        }
        $db = $this->_getWriteAdapter();
        /* @var $res Mage_Core_Model_Resource */
        $res = Mage::getSingleton('core/resource');
        /* @var $dbHelper Mana_Db_Helper_Data */
        $dbHelper = Mage::helper('mana_db');
        /* @var $formulaHelper Mana_Db_Helper_Formula */
        $formulaHelper = Mage::helper('mana_db/formula');


        // get basic select from all source tables, properly joined (based on m_db.xml)
        /** @noinspection PhpUndefinedFieldInspection */
        $entity = (string)$scope->flattens;
        //$db->query($formulaHelper->delete($entity, $targetEntity));

        // get formula hashes and formula texts
        $formulaGroups = $formulaHelper->getFormulaGroups($targetEntity, $options);

        // for each formula hash => formula text
        foreach ($formulaGroups as $formulas) {
            $formulas = $formulas ? json_decode($formulas, true) : array();

            // filter basic select by formula hash
            $context = $formulaHelper->select($targetEntity, $formulas, $options);

            // convert SELECT into UPDATE which acts as INSERT on DUPLICATE unique keys
            $sql = $context->getSelect()->insertFromSelect(
                $res->getTableName($dbHelper->getScopedName($targetEntity)),
                $context->getFields());

            // run the statement
            try {
                $db->query($sql);
            }
            catch (Exception $e) {
                /* @var $logger Mana_Core_Helper_Logger */
                $logger = Mage::helper('mana_core/logger');
                $logger->logDbIndexerFailure($sql);
                throw $e;
            }

        }
    }

    protected function _getUpdateSelect($basicSelect, $formulaHash) {
        $select = clone $basicSelect;

        return $select;
    }
}