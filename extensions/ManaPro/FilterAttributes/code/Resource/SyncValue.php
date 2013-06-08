<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterAttributes
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_FilterAttributes_Resource_SyncValue  extends ManaPro_FilterAttributes_Resource_Type  {
    /**
     * @param ManaPro_FilterAttributes_Model_Indexer $indexer
     * @param array $options
     * @throws Mage_Core_Exception
     */
    public function process($indexer, $options){
        $db = $this->_getWriteAdapter();

        /* @var $res Mage_Core_Model_Resource */
        $res = Mage::getSingleton('core/resource');

        $t = Mage::helper("manapro_filterattributes");

        $filename = Mage::getBaseDir()."/app/etc/m-attribute-sync.xml";
        if (!file_exists($filename)) {
            return;
        }
        if (!($xml = simplexml_load_string(file_get_contents($filename)))) {
            throw new Mage_Core_Exception ($t->__("Invalid XML File %s", $filename));
        }

        foreach ($xml->children() as $syncXml) {
            $values = array();
            $sourceAttribute = false;
            $targetAttribute = false;
            if (($sourceAttributeCode = (string)$syncXml->source) && ($targetAttributeCode = (string)$syncXml->target)
                && $syncXml->value && ($sourceAttribute = $this->_getAttributeByCode($sourceAttributeCode)) &&
                    ($targetAttribute = $this->_getAttributeByCode($targetAttributeCode)))
            {
                foreach ($syncXml->value as $valueXml) {
                    if (($sourceLabel = (string)$valueXml->source) && $valueXml->target
                        && ($sourceId = $this->_getOptionIdByLabel($sourceAttribute['attribute_id'], $sourceLabel)))
                    {
                        $targets = array();
                        foreach ($valueXml->target as $targetXml) {
                            if (($targetLabel = (string)$targetXml)
                                && ($targetId = $this->_getOptionIdByLabel($targetAttribute['attribute_id'], $targetLabel)))
                            {
                                $targets[] = $targetId;
                            }
                        }
                        if (count($targets)) {
                            $values[$sourceId] = implode(',', $targets);
                        }
                    }
                }
            }

            if (count($values)) {
                $db->beginTransaction();

                try {
                    // DELETE all target values
                    $sourceAttributeTable = $sourceAttribute['backend_table']
                        ? $sourceAttribute['backend_table']
                        : 'catalog_product_entity_' . $sourceAttribute['backend_type'];
                    $targetAttributeTable = $targetAttribute['backend_table']
                        ? $targetAttribute['backend_table']
                        : 'catalog_product_entity_' . $targetAttribute['backend_type'];

                    $select = $db->select()
                        ->from($res->getTableName($targetAttributeTable))
                        ->where("`attribute_id` = ?", $targetAttribute['attribute_id']);

                    if (isset($options['product_id'])) {
                        $select->where("`entity_id` = ?", $options['product_id']);
                    }
                    $sql = $select->deleteFromSelect($res->getTableName($targetAttributeTable));

                    $db->query($sql);

                    // INSERT all target values which are assigned in source table
                    $fields = array(
                        'entity_type_id' => new Zend_Db_Expr("`v`.`entity_type_id`"),
                        'attribute_id' => new Zend_Db_Expr($targetAttribute['attribute_id']),
                        'store_id' => new Zend_Db_Expr("0"),
                        'entity_id' => new Zend_Db_Expr("`e`.`entity_id`"),
                        'value' => new Zend_Db_Expr($this->_getIfExpr("`v`.`value`", $values)),
                    );

                    /* @var $select Varien_Db_Select */
                    $select = $db->select()
                        ->from(array('e' => $this->getTable('catalog/product')), null)
                        ->joinInner(array('v' =>  $res->getTableName($sourceAttributeTable)),
                            $db->quoteInto("`e`.`entity_id` = `v`.`entity_id` AND `v`.`store_id` = 0 ".
                                "AND `v`.`attribute_id` = ?", $sourceAttribute['attribute_id']), null)
                        ->columns($fields);

                    if (isset($options['product_id'])) {
                        $select->where("`e`.`entity_id` = ?", $options['product_id']);
                    }

                    // convert SELECT into UPDATE which acts as INSERT on DUPLICATE unique keys
                    $sql = $select->insertFromSelect($res->getTableName($targetAttributeTable), array_keys($fields));

                    // run the statement
                    $db->query($sql);

                    $db->commit();
                }
                catch (Exception $e) {
                    $db->rollBack();
                    throw $e;
                }
            }
        }
    }

    public function clearAttributeValues ($targetAttributeCode, $options = array()){
        $options = array_merge(array(
            'type_filter' => false,
        ), $options);
        $db = $this->_getWriteAdapter();

        /* @var $res Mage_Core_Model_Resource */
        $res = Mage::getSingleton('core/resource');

        $t = Mage::helper("manapro_filterattributes");
        if ($targetAttribute = $this->_getAttributeByCode($targetAttributeCode)) {
             $db->beginTransaction();

            try {
                // DELETE all target values
                $targetAttributeTable = $targetAttribute['backend_table']
                    ? $targetAttribute['backend_table']
                    : 'catalog_product_entity_' . $targetAttribute['backend_type'];

                $sql = "DELETE FROM `{$res->getTableName($targetAttributeTable)}` WHERE `attribute_id` = {$targetAttribute['attribute_id']}";
                if ($options['type_filter']) {
                    if (!is_array($options['type_filter'])) {
                        $options['type_filter'] = array($options['type_filter']);
                    }
                    $typeIds = array();
                    foreach ($options['type_filter'] as $typeId) {
                        $typeIds[] = "'$typeId'";
                    }
                    $typeIds = implode(', ', $typeIds);
                    $sql .= " AND `entity_id` IN (SELECT `e`.`entity_id`
                                  FROM `{$this->getTable('catalog/product')}` AS `e`
                                 WHERE `e`.`type_id` IN ($typeIds))";
                }

                $db->query($sql);
                $db->commit();
                echo "Clearing values is completed";
            }
            catch (Exception $e) {
                $db->rollBack();
                throw $e;
            }
        }
    }

    /**
     * @param int $attributeId
     * @param string $label
     * @return int | bool
     */
    protected function _getOptionIdByLabel ( $attributeId, $label) {
        $db = $this->_getWriteAdapter();

        $select = $db->select()
            ->from(array('o' => $this->getTable('eav/attribute_option')), array('option_id'))
            ->joinInner(array('v' =>$this->getTable('eav/attribute_option_value')),
                $db->quoteInto("`v`.`option_id` = `o`.`option_id` AND `v`.`store_id` = 0 AND `v`.`value` = ?", $label),
                null)
            ->where("`o`.`attribute_id` = ?", $attributeId);

        return $db->fetchOne($select);
    }
}