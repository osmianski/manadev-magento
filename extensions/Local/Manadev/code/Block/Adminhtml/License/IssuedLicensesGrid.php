<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Block_Adminhtml_License_IssuedLicensesGrid extends Mana_Admin_Block_V2_Grid {
    public function __construct() {
        parent::__construct();
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('desc');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareColumns() {
        $this->addColumn(
            'm_license_no',
            array(
                'header' => $this->__('License Number'),
                'index' => 'm_license_no',
                'width' => '100px',
                'align' => 'left',
            )
        );

        $this->addColumn(
            'customer_name',
            array(
                'header' => $this->__('Customer'),
                'index' => 'customer_name',
                'filter_index' => new Zend_Db_Expr("CONCAT(IFNULL(`cefn`.`value`, ''), ' ',IFNULL(`celn`.`value`, ''))"),
                'width' => '50px',
                'align' => 'left',
                'renderer' => 'local_manadev/adminhtml_renderer_customer',
            )
        );

        $this->addColumn(
            'order_number',
            array(
                'header' => $this->__('Order'),
                'index' => 'order_number',
                'filter_index' => 'order_increment_id',
                'width' => '50px',
                'align' => 'left',
                'renderer' => 'local_manadev/adminhtml_renderer_order',
            )
        );

        $this->addColumn(
            'product_name',
            array(
                'header' => $this->__('Product'),
                'index' => 'product_name',
                'width' => '200px',
                'align' => 'left',
                'link' => array('route' => 'adminhtml/catalog_product/edit', 'params' => array('id' => '{{product_id}}')),
                'renderer' => 'local_manadev/adminhtml_renderer_link',
            )
        );

        $this->addColumn(
            'm_registered_domain',
            array(
                'header' => $this->__('Registered URL'),
                'index' => 'm_registered_domain',
                'width' => '200px',
                'align' => 'left',
                'renderer' => 'local_manadev/adminhtml_renderer_registeredDomain',
            )
        );

        $this->addColumn(
            'actual_admin_panel_url',
            array(
                'header' => $this->__('Actual Admin Panel URL'),
                'index' => 'actual_admin_panel_url',
                'width' => '100px',
                'align' => 'left',
                'link' => array('link' => "{{actual_admin_panel_url}}"),
                'renderer' => 'local_manadev/adminhtml_renderer_link',
            )
        );

        $this->addColumn(
            'actual_frontend_urls',
            array(
                'header' => $this->__('Actual Frontend URL'),
                'index' => 'actual_frontend_urls',
                'width' => '100px',
                'align' => 'left',
                'renderer' => 'local_manadev/adminhtml_renderer_linkMultiline'
            )
        );

        $this->addColumn(
            'used_on_magento_ids',
            array(
                'header' => $this->__('Used On Magento IDs'),
                'index' => 'used_on_magento_ids',
                'width' => '100px',
                'align' => 'left',
                'renderer' => 'local_manadev/adminhtml_renderer_magentoIds'
            )
        );

        $this->addColumn(
            'used_at_ip_addresses',
            array(
                'header' => $this->__('Used At IP Addresses'),
                'index' => 'used_at_ip_addresses',
                'filter_index' => 'main_table.agg_remote_ips',
                'width' => '100px',
                'align' => 'left',
                'renderer' => 'local_manadev/adminhtml_renderer_multiline'
            )
        );

        $this->addColumn(
            'status',
            array(
                'header' => $this->__('Status'),
                'index' => 'status',
                'type' => 'select',
                'width' => '100px',
                'align' => 'left',
                'options' => Mage::getSingleton('local_manadev/download_status')->toOptionArray(),
                'renderer' => 'local_manadev/adminhtml_renderer_status',
            )
        );

        $this->addColumn(
            'm_support_valid_til',
            array(
                'header' => $this->__('Expire Date'),
                'index' => 'm_support_valid_til',
                'type' => 'datetime',
                'width' => '100px',
                'align' => 'left',
                'renderer' => 'local_manadev/adminhtml_renderer_date'
            )
        );

        $this->addColumn(
            'created_at',
            array(
                'header' => $this->__('Created At'),
                'index' => 'created_at',
                'type' => 'datetime',
                'width' => '50px',
                'align' => 'left',
            )
        );

        /** @var Mana_Core_Helper_Js $jsHelper */
        $jsHelper = Mage::helper('mana_core/js');

        $jsHelper->setConfig('url.saveLicense', $this->getUrl('adminhtml/license/saveLicenseInfo'));


        return parent::_prepareColumns();
    }

    public function getRowUrl($item) {
        return false;
    }

    protected function _prepareCollection() {
        $fn = Mage::getModel('eav/entity_attribute')->loadByCode('customer', 'firstname');
        $ln = Mage::getModel('eav/entity_attribute')->loadByCode('customer', 'lastname');

        /** @var Mage_Downloadable_Model_Resource_Link_Purchased_Item_Collection $collection */
        $collection = Mage::getResourceModel("downloadable/link_purchased_item_collection");
        $columns = array(
            new Zend_Db_Expr("`ce`.`entity_id` AS customer_id"),
            new Zend_Db_Expr("CONCAT(IFNULL(`cefn`.`value`, ''), ' ',IFNULL(`celn`.`value`, '')) AS customer_name"),
            new Zend_Db_Expr("`lp`.`order_increment_id` AS order_number"),
            new Zend_Db_Expr("`lp`.`order_id`"),
            new Zend_Db_Expr("`lp`.`product_name`"),
            'used_on_magento_ids' => new Zend_Db_Expr("`main_table`.`agg_magento_ids`"),
            'used_at_ip_addresses' => new Zend_Db_Expr("`main_table`.`agg_remote_ips`"),
        );

        $collection->getSelect()
            ->joinLeft(array('lp' => $collection->getTable('downloadable/link_purchased')), '`lp`.`purchased_id` = `main_table`.`purchased_id`', array())
            ->joinLeft(array('ce' => $collection->getTable('customer/entity')), '`ce`.`entity_id` = `lp`.`customer_id`', array())
            ->joinLeft(array('cefn' => $collection->getTable('customer/entity').'_varchar'), '`ce`.`entity_id` = `cefn`.`entity_id` AND `cefn`.`attribute_id` = '.$fn->getAttributeId(), array())
            ->joinLeft(array('celn' => $collection->getTable('customer/entity').'_varchar'), '`ce`.`entity_id` = `celn`.`entity_id` AND `celn`.`attribute_id` = '.$ln->getAttributeId(), array())
            ->joinLeft(array('mlr' => new Zend_Db_Expr("(
                SELECT license_verification_no, actual_admin_panel_url, agg_frontend_urls AS actual_frontend_urls
                FROM (
                    SELECT mlr.id, mlr.magento_id, mlr.`admin_url` AS actual_admin_panel_url, mle.license_verification_no, mlr.agg_frontend_urls, mlr.`remote_ip`
                    FROM ". $collection->getTable('local_manadev/license_request') ." mlr
                    INNER JOIN (SELECT magento_id, MAX(created_at) as created_at FROM ". $collection->getTable('local_manadev/license_request') ." GROUP BY magento_id) mlrl ON mlr.`magento_id` = mlrl.`magento_id` AND `mlr`.`created_at` = `mlrl`.`created_at`
                    INNER JOIN ". $collection->getTable('local_manadev/license_extension') ." mle ON mlr.id = mle.request_id
                    WHERE TRIM(license_verification_no) <> '' AND mle.license_verification_no IS NOT NULL
                ) AS tmp
                GROUP BY license_verification_no
                )")), '`mlr`.`license_verification_no` = `main_table`.`m_license_verification_no`', array('actual_admin_panel_url', 'actual_frontend_urls'))
            ->columns($columns);
        $sql = $collection->getSelectSql(true);
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    public function getGridUrl() {
        return $this->adminHelper()->getStoreUrl('*/*/issuedLicensesGrid');
    }
}