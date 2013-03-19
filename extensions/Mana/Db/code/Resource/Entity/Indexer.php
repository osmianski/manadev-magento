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
    public function flattenGlobalScope($indexer, $target, $scope, $options) {
        $db = $this->_getWriteAdapter();
        /* @var $res Mage_Core_Model_Resource */
        $res = Mage::getSingleton('core/resource');
        /* @var $dbHelper Mana_Db_Helper_Data */
        $dbHelper = Mage::helper('mana_db');

        // get basic select from all source tables, properly joined (based on m_db.xml)
        $basicSelect = $this->_getBasicSelect();

        // get formula hashes and formula texts
        $formulaGroupSelect = $this->_getFormulaGroupSelect();

        // for each formula hash => formula text
        foreach ($db->fetchPairs($formulaGroupSelect) as $formulaHash => $formulas) {
            $formulas = $formulas ? json_decode($formulas, true) : array();

            // filter basic select by formula hash
            $select = $this->_getUpdateSelect($basicSelect, $formulaHash);
            $fields = array();

            foreach ($scope->fields->children() as $fieldName => $fieldXml) {
                if (isset($fieldXml->no)) {
                    // prepare select expression based on default mask, default formula and direct value
                    //      (process formula language into SQL)
                }
                else {
                    // add direct value from main source table
                }
            }
            foreach ($this->_getSystemFields() as $fieldName => $field) {
                // add column for system field
            }

            // convert SELECT into UPDATE which acts as INSERT on DUPLICATE unique keys
            $sql = $select->insertFromSelect($res->getTableName($dbHelper->getScopedName((string)$target->entity)), $fields);

            // run the statement
            $db->query($sql);
        }
    }

    /**
     * @param Mana_Db_Model_Entity_Indexer $indexer
     * @param Varien_Simplexml_Element $target
     * @param Varien_Simplexml_Element $scope
     * @param array $options
     * @return void
     */
    public function flattenStoreScope($indexer, $target, $scope, $options) {
    }

    protected function _getUpdateSelect($basicSelect, $formulaHash) {
        $select = clone $basicSelect;

        return $select;
    }

    protected function _getBasicSelect() {
        $db = $this->_getWriteAdapter();
        $select = $db->select();
        return $select;
    }
}