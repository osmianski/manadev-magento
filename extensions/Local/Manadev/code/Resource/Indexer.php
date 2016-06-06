<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Resource_Indexer extends Mage_Core_Model_Mysql4_Abstract
{

    /**
     * Resource initialization
     */
    protected function _construct() {
        $this->_setResource('local_manadev');
    }

    public function process($options){
        if(
            !isset($options['item_id']) &&
            !isset($options['reindex_all'])
        ) {
            return;
        }

        $platformAttr = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'platform');
        $priceAttr = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'price');
        $date = Mage::app()->getLocale()->date();
        $currentDate = date("Y-m-d", $date->getTimestamp());


        $multi_fields = array(
            array(
                'item_id' => '`lpi`.`item_id`',
                'status' =>
                "IF(TRIM(`lpi`.`m_registered_domain`) = '' AND TRIM(`lpi`.`m_store_info`) = '' AND `lpi`.`status` <> '". Local_Manadev_Model_Download_Status::M_LINK_STATUS_NOT_REGISTERED ."',
                    '". Local_Manadev_Model_Download_Status::M_LINK_STATUS_NOT_REGISTERED."',
                    `lpi`.`status`
                )"
            ),
            array(
                'item_id' => '`lpi`.`item_id`',
                'status' => "IF( IFNULL(`pl`.`value`, '" . Local_Manadev_Model_Platform::VALUE_MAGENTO_1 . "') = '" . Local_Manadev_Model_Platform::VALUE_MAGENTO_1 . "',
                    IF(`lpi`.`status` = '" . Local_Manadev_Model_Download_Status::M_LINK_STATUS_AVAILABLE . "' AND `lpi`.`m_support_valid_til` <= '{$currentDate}',
                        '" . Local_Manadev_Model_Download_Status::M_LINK_STATUS_PERIOD_EXPIRED . "',
                        IF(`lpi`.`status` = '" . Local_Manadev_Model_Download_Status::M_LINK_STATUS_PERIOD_EXPIRED . "' AND `lpi`.`m_support_valid_til` > '{$currentDate}',
                            '" . Local_Manadev_Model_Download_Status::M_LINK_STATUS_AVAILABLE . "',
                            `lpi`.`status`
                        )
                    ),
                    IF(`lpi`.`status` = '" . Local_Manadev_Model_Download_Status::M_LINK_STATUS_AVAILABLE_TIL . "' AND `lpi`.`m_support_valid_til` <= '{$currentDate}',
                        '" . Local_Manadev_Model_Download_Status::M_LINK_STATUS_DOWNLOAD_EXPIRED . "',
                        IF(`lpi`.`status` = '" . Local_Manadev_Model_Download_Status::M_LINK_STATUS_DOWNLOAD_EXPIRED . "' AND `lpi`.`m_support_valid_til` > '{$currentDate}',
                            '" . Local_Manadev_Model_Download_Status::M_LINK_STATUS_AVAILABLE_TIL . "',
                            `lpi`.`status`
                        )
                    )
                )"
            ),
            array(
                'item_id' => '`lpi`.`item_id`',
                'status' => "IF( IFNULL(`pl`.`value`, '".Local_Manadev_Model_Platform::VALUE_MAGENTO_1."') = '".Local_Manadev_Model_Platform::VALUE_MAGENTO_1."',
                    IF(`lpi`.`status` = '". Local_Manadev_Model_Download_Status::M_LINK_STATUS_AVAILABLE ."' AND `lpi`.`m_support_valid_til` <= '{$currentDate}',
                        '". Local_Manadev_Model_Download_Status::M_LINK_STATUS_PERIOD_EXPIRED ."',
                        IF(`lpi`.`status` = '" . Local_Manadev_Model_Download_Status::M_LINK_STATUS_PERIOD_EXPIRED . "' AND `lpi`.`m_support_valid_til` > '{$currentDate}',
                            '" . Local_Manadev_Model_Download_Status::M_LINK_STATUS_AVAILABLE . "',
                            `lpi`.`status`
                        )
                    ),
                    IF(`lpi`.`status` = '" . Local_Manadev_Model_Download_Status::M_LINK_STATUS_AVAILABLE_TIL . "' AND `lpi`.`m_support_valid_til` <= '{$currentDate}',
                        '" . Local_Manadev_Model_Download_Status::M_LINK_STATUS_DOWNLOAD_EXPIRED . "',
                        IF(`lpi`.`status` = '" . Local_Manadev_Model_Download_Status::M_LINK_STATUS_DOWNLOAD_EXPIRED . "' AND `lpi`.`m_support_valid_til` > '{$currentDate}',
                            '" . Local_Manadev_Model_Download_Status::M_LINK_STATUS_AVAILABLE_TIL . "',
                            `lpi`.`status`
                        )
                    )
                )"
            ),
            array(
                'item_id' => '`lpi`.`item_id`',
                'status' => "IF(`price`.`value` = 0,
                    IF(IFNULL(`pl`.`value`, '" . Local_Manadev_Model_Platform::VALUE_MAGENTO_1 . "') = '" . Local_Manadev_Model_Platform::VALUE_MAGENTO_1 . "',
                        '". Local_Manadev_Model_Download_Status::M_LINK_STATUS_AVAILABLE . "',
                        '". Local_Manadev_Model_Download_Status::M_LINK_STATUS_AVAILABLE_TIL. "'
                    ),
                    `lpi`.`status`
                )",
            )
        );

        $db = $this->_getWriteAdapter();
        foreach($multi_fields as $fields) {
            $select = $db->select();

            $select
                ->from(array('lpi' => $this->getTable(Local_Manadev_Model_Downloadable_Item::ENTITY)), null)
                ->joinLeft(array('price' => $this->getTable('catalog/product') . '_decimal'), "`lpi`.`product_id` = `price`.`entity_id` AND `price`.`attribute_id` = " . $priceAttr->getId(), null)
                ->joinLeft(array('pl' => $this->getTable('catalog/product').'_int'), "`lpi`.`product_id` = `pl`.`entity_id` AND `pl`.`attribute_id` = ". $platformAttr->getId(), null);

            $select->columns($this->dbHelper()->wrapIntoZendDbExpr($fields));

            if(isset($options['item_id'])) {
                $select->where("`lpi`.`item_id` = ?", $options['item_id']);
            }

            $selectSql = $select->__toString();
            $sql = $select->insertFromSelect($this->getTable(Local_Manadev_Model_Downloadable_Item::ENTITY), array_keys($fields));
            $db->exec($sql);
        }
    }

    public function reindexAll(){
        $this->process(array('reindex_all' => true));
    }

    /**
     * @return Mana_Core_Helper_Db
     */
    public function dbHelper() {
        return Mage::helper('mana_core/db');
    }
}