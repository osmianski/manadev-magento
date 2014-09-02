<?php
/** 
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Core_Helper_Eav extends Mage_Core_Helper_Abstract {
    protected $_attributes;

    public function getAttributes()
    {
        if (is_null($this->_attributes)) {
            $this->_attributes  = Mage::getSingleton('eav/config')
                ->getEntityType('catalog_product')
                ->getAttributeCollection();
        }

        return $this->_attributes;
    }

    /**
     * @param Varien_Db_Select $select
     * @param string $attributeCode
     * @return $this
     */
    public function joinAttribute($select, $attributeCode) {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper(strtolower('Mana_Core'));

        /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
        $attribute = $core->collectionFind($this->getAttributes(), 'attribute_code', $attributeCode);

        /* @var $db Varien_Db_Adapter_Pdo_Mysql */
        $db = $select->getAdapter();

        $alias = 'meav_'.$attributeCode;
        $storeAlias = 's' . $alias;
        $from = $select->getPart(Varien_Db_Select::FROM);
        if (!isset($from[$alias])) {
            $select->joinLeft(array($alias => $attribute->getBackendTable()),
                implode(' AND ', array(
                    "`$alias`.`entity_id` = `e`.`entity_id`",
                    $db->quoteInto("`$alias`.`attribute_id` = ?", $attribute->getId()),
                    "`$alias`.`store_id` = 0",
                )), null);
            $select->joinLeft(array($storeAlias => $attribute->getBackendTable()),
                implode(' AND ', array(
                    "`$storeAlias`.`entity_id` = `e`.`entity_id`",
                    $db->quoteInto("`$storeAlias`.`attribute_id` = ?", $attribute->getId()),
                    $db->quoteInto("`$storeAlias`.`store_id` = ?", Mage::app()->getStore()->getId()),
                )), null);
        }

        return $this;
    }

    public function attributeValue($attributeCode) {
        $alias = 'meav_' . $attributeCode;
        $storeAlias = 's' . $alias;

        return "COALESCE(`$storeAlias`.`value`, `$alias`.`value`)";
    }
}