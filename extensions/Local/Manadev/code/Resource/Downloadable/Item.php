<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Resource_Downloadable_Item extends Mage_Downloadable_Model_Resource_Link_Purchased_Item
{
    public function getItemsByComposerRepoId($repoId) {
        $db = $this->getReadConnection();

        $platformAttr = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'platform');
        $nameAttr = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'name');

        // customer_firstname
        return $db->fetchAll($db->select()
            ->from(array('di' => $this->getMainTable()), array(
                'item_id',
                'product_id',
                'status',
                'm_license_verification_no',
                'm_key_public',
                'm_key_private',
            ))
            ->join(array('p' => $this->getTable('catalog/product')),
                "`p`.`entity_id` = `di`.`product_id`", 'sku')
            ->join(array('pl' => $this->getTable('catalog/product').'_int'),
                "`pl`.`entity_id` = `p`.`entity_id` AND `pl`.`store_id` = 0 AND `pl`.`attribute_id` = ". $platformAttr->getId(), null)
            ->join(array('name' => $this->getTable('catalog/product').'_varchar'),
                "`name`.`entity_id` = `p`.`entity_id` AND `name`.`store_id` = 0 AND `name`.`attribute_id` = ". $nameAttr->getId(), null)
            ->join(array('oi' => 'sales_flat_order_item'), "`oi`.`item_id` = `di`.`order_item_id`", null)
            ->join(array('o' => 'sales_flat_order'), "`oi`.`order_id` = `o`.`entity_id`", array(
                'customer_firstname',
                'customer_email',
            ))
            ->where("`di`.`composer_repo_id` = ?", $repoId)
            ->columns(array(
                'platform' => new Zend_Db_Expr("`pl`.`value`"),
                'name' => new Zend_Db_Expr("`name`.`value`"),
            ))
        );
    }

    public function increaseCounterForExtension($extension) {
        $db = $this->_getWriteAdapter();

        $db->query("UPDATE `{$this->getMainTable()}` " .
            "SET `number_of_downloads_used` = `number_of_downloads_used` + 1 " .
            $db->quoteInto("WHERE `item_id` = ?", $extension->getData('item_id')));
    }

    public function upgradeAggregateByLicenseVerificationNos($licenseVerificationNos = array()) {
        $licenseVerificationNos = array_filter($licenseVerificationNos);
        foreach($licenseVerificationNos as $licenseVerificationNo) {
            $cols = array(
                'magento_id',
                'remote_ip'
            );
            $select = $this->_getWriteAdapter()->select();
            $select->from(array('main_table' => $this->getTable('local_manadev/license_request')), array())
                ->join(array('mlr' => new Zend_Db_Expr("(
                    SELECT magento_id, id, MAX(created_at) AS created_at FROM ". $this->getTable('local_manadev/license_request') ." GROUP BY magento_id
                )")), "`main_table`.`magento_id` = mlr.`magento_id` AND `main_table`.`created_at` = `mlr`.`created_at`", array())
                ->joinInner(array('mle' => $this->getTable('local_manadev/license_extension')), 'mle.request_id = main_table.id', array())
                ->where('mle.license_verification_no = ?', $licenseVerificationNo)
                ->columns($cols)
                ;
            $magento_ids = array();
            $remote_ips = array();
            foreach($this->_getReadAdapter()->fetchAll($select) as $row) {
                $magento_ids[] = $row['magento_id'];
                $remote_ips[] = $row['remote_ip'];
            }

            $magento_ids = implode('|', $magento_ids);
            $remote_ips = implode('|', array_unique($remote_ips));

            $this->_getWriteAdapter()->update($this->getTable('downloadable/link_purchased_item'), array('agg_remote_ips' => $remote_ips, 'agg_magento_ids' => $magento_ids), $this->_getWriteAdapter()->quoteInto('m_license_verification_no = ?', $licenseVerificationNo));
        }
    }

    public function afterSaveCommit(Mage_Core_Model_Abstract $object) {
        $this->upgradeAggregateByLicenseVerificationNos(array($object->getData('m_license_verification_no')));
        return parent::_afterSave($object);
    }
}