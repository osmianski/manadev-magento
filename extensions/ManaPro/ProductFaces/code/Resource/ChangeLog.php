<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class ManaPro_ProductFaces_Resource_ChangeLog extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct() {
        $this->_setResource('manapro_productfaces');
    }

    public function createTriggerIfNotExists() {
        $this->dropTriggerIfExists();
        if ($this->_triggerExists()) {
            return;
        }

        $db = $this->_getWriteAdapter();
        /* @var Mage_Core_Model_Resource $res */
        $res = Mage::getSingleton('core/resource');

    	$linkTypeId = Mage::getResourceModel('manapro_productfaces/collection')->getRepresentingLinkTypeId();
        $packQtyAttributeId = $db->fetchOne($db->select()
            ->from($res->getTableName('catalog_product_link_attribute'), 'product_link_attribute_id')
            ->where("`link_type_id` = ?", $linkTypeId)
            ->where("`product_link_attribute_code` = ?", 'm_pack_qty')
        );

        $sql = <<<EOT

CREATE TABLE IF NOT EXISTS `{$res->getTableName('m_inventory_change_log')}` (
    `product_id` int(10) unsigned NOT NULL,
    `delta` decimal(12,4) NOT NULL DEFAULT '0.0000',
    PRIMARY KEY `product_id` (`product_id`),
    CONSTRAINT `{$res->getTableName('m_inventory_change_log_fk')}` FOREIGN KEY (`product_id`) 
        REFERENCES `{$res->getTableName('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
);

EOT;
        $db->query($sql);
        $sql = <<<EOT

CREATE TRIGGER `{$res->getTableName('m_inventory_change_tracker')}` 
AFTER UPDATE ON `{$res->getTableName('cataloginventory_stock_item')}`
FOR EACH ROW
BEGIN
    IF OLD.`qty` <> NEW.`qty` THEN 
        SELECT `link`.`product_id`, `pack_qty`.`value`
        FROM `{$res->getTableName('catalog_product_link')}` AS `link`
        LEFT OUTER JOIN `{$res->getTableName('catalog_product_link_attribute_int')}` AS `pack_qty`
            ON `pack_qty`.`link_id` = `link`.`link_id` AND `pack_qty`.`product_link_attribute_id` = $packQtyAttributeId
        WHERE `link`.`linked_product_id` = NEW.`product_id` AND 
            `link`.`link_type_id` = {$linkTypeId}
        LIMIT 1
        INTO @product_id, @pack_qty;
        
        SET @pack_qty = COALESCE(@pack_qty, 1);
        SET @product_id = COALESCE(@product_id, NEW.`product_id`);

        IF @product_id = NEW.`product_id` THEN
            INSERT IGNORE INTO `{$res->getTableName('m_inventory_change_log')}` (`product_id`) 
            VALUES (@product_id);
        ELSE
            INSERT INTO `{$res->getTableName('m_inventory_change_log')}` (`product_id`, `delta`) 
            VALUES (@product_id, (NEW.`qty` - OLD.`qty`) * @pack_qty)
            ON DUPLICATE KEY UPDATE `delta` = `delta` + (NEW.`qty` - OLD.`qty`) * @pack_qty;
        END IF;

    END IF;
END;

EOT;
        $db->query($sql);
    }

    public function dropTriggerIfExists() {
        if (!$this->_triggerExists()) {
            return;
        }

        $db = $this->_getWriteAdapter();
        /* @var Mage_Core_Model_Resource $res */
        $res = Mage::getSingleton('core/resource');

        $db->query("DROP TABLE IF EXISTS `{$res->getTableName('m_inventory_change_log')}`");
        $db->query("DROP TRIGGER IF EXISTS `{$res->getTableName('m_inventory_change_tracker')}`");
    }

    public function getPendingProductCount() {
        $db = $this->_getWriteAdapter();
        /* @var Mage_Core_Model_Resource $res */
        $res = Mage::getSingleton('core/resource');

        return $db->fetchOne($db->select()
            ->from($res->getTableName('m_inventory_change_log'),
                new Zend_Db_Expr("COUNT(*)"))
        );
    }

    public function deleteProductIdFromChangeLog($productId) {
        if (!Mage::getStoreConfigFlag('manapro_productfaces/inventory_change_log/is_enabled')) {
            return;
        }

        $db = $this->_getWriteAdapter();
        /* @var Mage_Core_Model_Resource $res */
        $res = Mage::getSingleton('core/resource');

        $db->delete($res->getTableName('m_inventory_change_log'), "product_id = $productId");
    }

    public function run($environment) {
        try {
            $startedAt = microtime(true);

            $db = $this->_getWriteAdapter();
            /* @var Mage_Core_Model_Resource $res */
            $res = Mage::getSingleton('core/resource');

            $changes = $db->fetchPairs($db->select()
                ->from($res->getTableName('m_inventory_change_log'), array('product_id', 'delta'))
            );

            /* @var ManaPro_ProductFaces_Resource_Inventory $inventory */
            $inventory = Mage::getResourceModel('manapro_productfaces/inventory');

            /* @var ManaPro_ProductFaces_Resource_Link $link */
            $link = Mage::getResourceModel('manapro_productfaces/link');

            foreach ($changes as $productId => $delta) {
                $db->query("
                    UPDATE `{$res->getTableName('cataloginventory_stock_item')}`
                    SET `qty` = `qty` + $delta
                    WHERE `product_id` = $productId  
                ");
                if ($link->isRepresentedProduct($productId)) {
                    $inventory->updateRepresentingProducts($productId);
                }
                else {
                    $inventory->updateStockProductMReprepresendedQty($productId);
                }
            }

            $result = count($changes);
            $elapsed = round(microtime(true) - $startedAt, 1);
            if (Mage::getStoreConfigFlag('manapro_productfaces/inventory_change_log/log')) {
                Mage::log("$environment: $result recalculated, " .
                    "{$this->getPendingProductCount()} pending, " .
                    "time elapsed: $elapsed s", Zend_Log::DEBUG, 'm_inventory_change.log');
            }

            return $result;
        }
        catch (Exception $e) {
            if (Mage::getStoreConfigFlag('manapro_productfaces/inventory_change_log/log')) {
                Mage::log("$environment error: {$e->getMessage()}", Zend_Log::DEBUG, 'm_inventory_change.log');
                Mage::log("{$e->getTraceAsString()}", Zend_Log::DEBUG, 'm_inventory_change.log');
            }
            throw $e;
        }
    }

    protected function _triggerExists() {
        /* @var Mage_Core_Model_Resource $res */
        $res = Mage::getSingleton('core/resource');

        return $this->_getWriteAdapter()->isTableExists($res->getTableName('m_inventory_change_log'));
    }
}