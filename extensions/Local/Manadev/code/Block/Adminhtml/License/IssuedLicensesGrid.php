<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Block_Adminhtml_License_IssuedLicensesGrid extends Mana_Admin_Block_V2_Grid {
    public function __construct() {
        parent::__construct();
        $this->setDefaultSort('m_support_last_purchased_at');
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
                'filter_condition_callback' => array($this, '_filterLicenseNo'),
                'width' => '100px',
                'align' => 'left',
            )
        );

        $this->addColumn(
            'customer_name',
            array(
                'header' => $this->__('Customer'),
                'index' => 'customer_name',
                'filter_index' => new Zend_Db_Expr($this->_getCustomerNameExpr()),
                'width' => '50px',
                'align' => 'left',
                'renderer' => 'local_manadev/adminhtml_renderer_customer',
            )
        );

        $this->addColumn(
            'order_increment_id',
            array(
                'header' => $this->__('Order'),
                'index' => 'order_increment_id',
                'filter_index' => new Zend_Db_Expr($this->_getOrderNoExpr()),
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
            'm_support_last_purchased_at',
            array(
                'header' => $this->__('Last Purchased Support At'),
                'index' => 'm_support_last_purchased_at',
                'type' => 'datetime',
                'width' => '50px',
                'align' => 'left',
                'frame_callback' => array($this, 'styleDate')
            )
        );

        /** @var Mana_Core_Helper_Js $jsHelper */
        $jsHelper = Mage::helper('mana_core/js');

        $jsHelper->setConfig('url.saveLicense', $this->getUrl('adminhtml/license/saveLicenseInfo'));


        return parent::_prepareColumns();
    }

    public function styleDate($value, $row, $column, $isExport) {
        $locale = Mage::app()->getLocale();
        $date = $locale->date($value, $locale->getDateFormat(), $locale->getLocaleCode(), false)->toString($locale->getDateFormat());

        return $date;
    }

    public function getRowUrl($item) {
        return false;
    }

    protected function _prepareCollection() {
        $fn = Mage::getModel('eav/entity_attribute')->loadByCode('customer', 'firstname');
        $ln = Mage::getModel('eav/entity_attribute')->loadByCode('customer', 'lastname');
        $pn = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'name');

        /** @var Mage_Downloadable_Model_Resource_Link_Purchased_Item_Collection $collection */
        $collection = Mage::getResourceModel("downloadable/link_purchased_item_collection");
        $customerNameExpr = $this->_getCustomerNameExpr();
        $orderNoExpr = $this->_getOrderNoExpr();
        $columns = array(
            new Zend_Db_Expr("`ce`.`entity_id` AS customer_id"),
            new Zend_Db_Expr("{$customerNameExpr} AS customer_name"),
            new Zend_Db_Expr("{$orderNoExpr} AS order_increment_id"),
            new Zend_Db_Expr("`lp`.`order_id`"),
            new Zend_Db_Expr("`pn`.`value` as product_name"),
            'used_on_magento_ids' => new Zend_Db_Expr("`main_table`.`agg_magento_ids`"),
            'used_at_ip_addresses' => new Zend_Db_Expr("`main_table`.`agg_remote_ips`"),
        );

        $collection->getSelect()
            ->join(array('pn' => $collection->getTable('catalog/product').'_varchar'), '`pn`.`entity_id` = `main_table`.`product_id` AND `pn`.`store_id` = 0 AND `pn`.`attribute_id` = '.$pn->getAttributeId(), array())
            ->joinLeft(array('oi' => $collection->getTable('sales/order_item')), '`oi`.`item_id` = `main_table`.`order_item_id`', array())
            ->joinLeft(array('o' => $collection->getTable('sales/order')), '`oi`.`order_id` = `o`.`entity_id`', array())
            ->joinLeft(array('lp' => $collection->getTable('downloadable/link_purchased')), '`lp`.`purchased_id` = `main_table`.`purchased_id`', array())
            ->joinLeft(array('ce' => $collection->getTable('customer/entity')), '`ce`.`entity_id` = COALESCE(`lp`.`customer_id`, `main_table`.`m_free_customer_id`)', array())
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
            // Coalesce so that free licenses without orders will be displayed
            ->where('COALESCE(`o`.`status`, "complete") = "complete"')
            ->columns($columns);

        $sql = $collection->getSelectSql(true);
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    public function getGridUrl() {
        return $this->adminHelper()->getStoreUrl('*/*/issuedLicensesGrid');
    }

    protected function _getNotLoggedInStr() {
        return $this->localHelper()->getLoggedNotInLabel();
    }

    /**
     * @return string
     */
    protected function _getCustomerNameExpr() {
        $customerNameExpr = "CONCAT(
            IFNULL(`cefn`.`value`, ''), 
            ' ',
            IFNULL(`celn`.`value`, '')
        )";
        return "IF(TRIM({$customerNameExpr}) = '', '" . $this->_getNotLoggedInStr() . "', {$customerNameExpr})";
    }

    /**
     * @return Local_Manadev_Helper_Data
     */
    protected function localHelper() {
        return Mage::helper('local_manadev');
    }

    protected function _getNoOrderStr() {
        return $this->localHelper()->getNoOrderStr();
    }

    /**
     * @return string
     */
    protected function _getOrderNoExpr() {
        return "COALESCE(`lp`.`order_increment_id`, '{$this->_getNoOrderStr()}')";
    }

    protected function _filterLicenseNo($collection, $column) {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $condition = new Zend_Db_Expr($read->quoteInto("`main_table`.`m_license_no` = ? OR `main_table`.`m_license_verification_no` = ?", $value, $value));
        $this->getCollection()->addFilter('m_license_no',$condition, 'string');
    }
}