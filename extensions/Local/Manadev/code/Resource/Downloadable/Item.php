<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Resource_Downloadable_Item extends Mage_Downloadable_Model_Resource_Link_Purchased_Item
{
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

    protected function _afterSave(Mage_Core_Model_Abstract $object) {
        $this->upgradeAggregateByLicenseVerificationNos(array($object->getData('m_license_verification_no')));
        return parent::_afterSave($object);
    }
}