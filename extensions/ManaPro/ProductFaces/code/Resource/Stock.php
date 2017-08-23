<?php
/**
 * @category    Mana
 * @package     ManaPro_ProductFaces
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_ProductFaces_Resource_Stock extends Mage_CatalogInventory_Model_Mysql4_Stock {
    public function correctItemsQty($stock, $productQtys, $operator = '-') {
        if (empty($productQtys)) {
            return $this;
        }
        $query = 'UPDATE ' . $this->getTable('cataloginventory/stock_item') . ' SET `m_represented_qty`=CASE `product_id` ';
        foreach ($productQtys as $productId => $qty) {
            $query .= $this->_getWriteAdapter()->quoteInto(' WHEN ? ', $productId);
            $query .= $this->_getWriteAdapter()->quoteInto(' THEN `qty`' . $operator . '? ', $qty);
        }
        $query .= ' ELSE `qty` END';
        $query .= ', `qty` =CASE `product_id` ';
        foreach ($productQtys as $productId => $qty) {
            $query .= $this->_getWriteAdapter()->quoteInto(' WHEN ? ', $productId);
            $query .= $this->_getWriteAdapter()->quoteInto(' THEN `qty`' . $operator . ' ? ', $qty);
        }
        $query .= ' ELSE `qty` END';
        $query .= $this->_getWriteAdapter()->quoteInto(' WHERE `product_id` IN (?)', array_keys($productQtys));
        $query .= $this->_getWriteAdapter()->quoteInto(' AND `stock_id` =?', $stock->getId());

        $entitySql = 'UPDATE ' . $this->getTable('catalog/product') . ' SET `m_represented_qty`=CASE `entity_id` ';
        foreach ($productQtys as $productId => $qty) {
            $entitySql .= $this->_getWriteAdapter()->quoteInto(' WHEN ? ', $productId);
            $entitySql .= $this->_getWriteAdapter()->quoteInto(' THEN `m_represented_qty`' . $operator . '? ', $qty);
        }
        $entitySql .= ' ELSE `m_represented_qty` END';
        $entitySql .= $this->_getWriteAdapter()->quoteInto(' WHERE `entity_id` IN (?)', array_keys($productQtys));

        $this->_getWriteAdapter()->beginTransaction();
        $this->_getWriteAdapter()->multi_query($query);
        $this->_getWriteAdapter()->multi_query($entitySql);
        Mage::helper('manapro_productfaces')->logQtyChanges("$query\n\n$entitySql");

        $inventoryResource = Mage::getResourceSingleton('manapro_productfaces/inventory');
        $inventoryResource->updateTextQties(array_keys($productQtys));

        $this->_getWriteAdapter()->commit();
        return $this;
    }
}