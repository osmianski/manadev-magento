<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Block_Adminhtml_License_MagentoInstancesGrid extends Mana_Admin_Block_V2_Grid {
    public function __construct() {
        parent::__construct();
        $this->setDefaultSort('magento_id');
        $this->setDefaultDir('asc');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(false);
    }

    protected function _prepareColumns() {
        $this->addColumn(
            'magento_id',
            array(
                'header' => $this->__('Magento ID'),
                'index' => 'magento_id',
                'filter_index' => 'main_table.magento_id',
                'width' => '100px',
                'align' => 'left',
            )
        );

        $this->addColumn(
            'remote_ip',
            array(
                'header' => $this->__('IP'),
                'index' => 'remote_ip',
                'width' => '100px',
                'align' => 'left',
            )
        );

        $this->addColumn(
            'customer_names',
            array(
                'header' => $this->__('Customers'),
                'index' => 'customer_names',
                'width' => '50px',
                'align' => 'left',
                'renderer' => 'local_manadev/adminhtml_renderer_customers',
            )
        );

        $this->addColumn(
            'order_numbers',
            array(
                'header' => $this->__('Orders'),
                'index' => 'order_numbers',
                'width' => '50px',
                'align' => 'left',
                'renderer' => 'local_manadev/adminhtml_renderer_orders',
            )
        );

        $this->addColumn(
            'extensions',
            array(
                'header' => $this->__('Paid Extensions'),
                'index' => 'extensions',
                'width' => '50px',
                'align' => 'left',
                'renderer' => 'local_manadev/adminhtml_renderer_multiline',
            )
        );

        $this->addColumn(
            'modules',
            array(
                'header' => $this->__('Modules'),
                'index' => 'agg_modules',
                'width' => '50px',
                'align' => 'left',
                'renderer' => 'local_manadev/adminhtml_renderer_multiline',
            )
        );

        $this->addColumn(
            'admin_url',
            array(
                'header' => $this->__('Admin Panel URL'),
                'index' => 'admin_url',
                'width' => '100px',
                'align' => 'left',
            )
        );

        $this->addColumn(
            'frontend_urls',
            array(
                'header' => $this->__('Frontend URLs'),
                'index' => 'agg_frontend_urls',
                'width' => '50px',
                'align' => 'left',
                'renderer' => 'local_manadev/adminhtml_renderer_multiline',
            )
        );

        $this->addColumn(
            'themes',
            array(
                'header' => $this->__('Themes'),
                'index' => 'agg_themes',
                'width' => '50px',
                'align' => 'left',
                'renderer' => 'local_manadev/adminhtml_renderer_multiline',
            )
        );

        $this->addColumn(
            'license_numbers',
            array(
                'header' => $this->__('License Numbers'),
                'index' => 'license_numbers',
                'width' => '50px',
                'align' => 'left',
                'renderer' => 'local_manadev/adminhtml_renderer_licenseNumbers',
            )
        );

        $this->addColumn(
            'history',
            array(
                'header' => $this->__('History'),
                'width' => '100px',
                'align' => 'left',
                'filter' => false,
                'renderer' => 'local_manadev/adminhtml_renderer_history',
            )
        );

        return parent::_prepareColumns();
    }

    protected function _prepareCollection() {
        $fn = Mage::getModel('eav/entity_attribute')->loadByCode('customer', 'firstname');
        $ln = Mage::getModel('eav/entity_attribute')->loadByCode('customer', 'lastname');
        $productName = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'name');

        /** @var Local_Manadev_Resource_License_Request_Collection $collection */
        $collection = Mage::getResourceModel("local_manadev/license_request_collection");

        $collection->getSelect()
            ->join(array('mlr' => new Zend_Db_Expr("(
                SELECT magento_id, id, MAX(created_at) AS created_at FROM ". $collection->getTable('local_manadev/license_request') ." GROUP BY magento_id
            )")), "`main_table`.`magento_id` = mlr.`magento_id` AND `main_table`.`created_at` = `mlr`.`created_at`", array())
            ->joinLeft(array('e' => new Zend_Db_Expr("(
                SELECT
                    request_id,
                    GROUP_CONCAT(DISTINCT dlp.`order_increment_id` SEPARATOR '|') AS order_numbers,
                    GROUP_CONCAT(DISTINCT dlp.`order_id` SEPARATOR '|') as order_ids,
                    GROUP_CONCAT(dlp.`customer_id` SEPARATOR '|') as customer_ids,
                    GROUP_CONCAT(DISTINCT `pn`.`value` SEPARATOR '|') as extensions,
                    GROUP_CONCAT(TRIM(CONCAT(IFNULL(`cf`.`value`, ''), ' ', IFNULL(`cl`.`value`, '')))  SEPARATOR '|') AS customer_names,
                    GROUP_CONCAT(dlpi.m_license_no SEPARATOR '|') as license_numbers
                FROM " . $collection->getTable('local_manadev/license_extension') . " mle
                LEFT JOIN " . $collection->getTable('downloadable/link_purchased_item') . " dlpi ON dlpi.`m_license_verification_no` = mle.`license_verification_no`
                LEFT JOIN " . $collection->getTable('downloadable/link_purchased') . " dlp ON dlpi.`purchased_id` = dlp.`purchased_id`
                LEFT JOIN `" . $collection->getTable('catalog/product') . "_varchar` pn ON pn.`entity_id` = dlpi.`product_id` AND pn.`attribute_id` = ". $productName->getAttributeId() ."
                LEFT JOIN `". $collection->getTable('customer/entity') ."_varchar` cf ON cf.`entity_id` = dlp.`customer_id` AND cf.`attribute_id` = ". $fn->getAttributeId() ."
                LEFT JOIN `". $collection->getTable('customer/entity') ."_varchar` cl ON cl.`entity_id` = dlp.`customer_id` AND cl.`attribute_id` = ". $ln->getAttributeId() ."
                WHERE `dlpi`.`status` <> '". Local_Manadev_Model_Download_Status::M_LINK_STATUS_NOT_AVAILABLE ."'
                GROUP BY request_id
            )")), "`e`.`request_id` = `main_table`.`id`", array('customer_ids', 'customer_names', 'order_numbers', 'order_ids', 'extensions', 'license_numbers'))
            ;
        $sql = $collection->getSelectSql(true);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    public function getGridUrl() {
        return $this->adminHelper()->getStoreUrl('*/*/magentoInstancesGrid');
    }

    public function getRowUrl($item) {
        return false;
    }

}