<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Block_Customer_Order_Grid extends Mage_Adminhtml_Block_Customer_Edit_Tab_Orders
{
    protected function _prepareColumns() {

        $this->addColumnAfter('status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'status',
            'type'  => 'options',
            'width' => '100px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ), 'grand_total');

//        $this->addColumnAfter(
//            'license_numbers',
//            array(
//                'header' => $this->__('License Numbers'),
//                'index' => 'license_numbers',
//                'filter_index' => 'ml.license_numbers',
//                'width' => '50',
//                'align' => 'center',
//                'renderer' => 'local_manadev/adminhtml_renderer_licenseNumbers',
//            ),
//            'status'
//        );
//
//        $this->addColumnAfter(
//            'magento_ids',
//            array(
//                'header' => $this->__('Magento IDs'),
//                'index' => 'magento_ids',
//                'filter_index' => 'ml.magento_ids',
//                'width' => '120',
//                'align' => 'center',
//                'renderer' => 'local_manadev/adminhtml_renderer_magentoIds',
//            ),
//            'license_numbers'
//        );
//
//        $this->addColumnAfter(
//            'remote_ips',
//            array(
//                'header' => $this->__('Magento IPs'),
//                'index' => 'remote_ips',
//                'filter_index' => 'ml.remote_ips',
//                'width' => '50',
//                'align' => 'center',
//                'renderer' => 'local_manadev/adminhtml_renderer_multiline',
//            ),
//            'magento_ids'
//        );

        return parent::_prepareColumns();
    }

    protected function _prepareCollection() {
        /* @var $collection Mage_Sales_Model_Resource_Order_Grid_Collection */
        $collection = Mage::getResourceModel('sales/order_grid_collection')
            ->addFieldToSelect('entity_id')
            ->addFieldToSelect('increment_id')
            ->addFieldToSelect('customer_id')
            ->addFieldToSelect('created_at')
            ->addFieldToSelect('grand_total')
            ->addFieldToSelect('order_currency_code')
            ->addFieldToSelect('store_id')
            ->addFieldToSelect('billing_name')
            ->addFieldToSelect('shipping_name')
            ->addFieldToFilter('customer_id', Mage::registry('current_customer')->getId())
            ->setIsCustomerMode(true);

        $collection->addFieldToSelect('status');

//        $collection->getSelect()
//        ->joinLeft(array('ml' => new Zend_Db_Expr("(
//            SELECT dlp.order_id,
//                GROUP_CONCAT(DISTINCT dlpi.m_license_no SEPARATOR '|') as license_numbers,
//                GROUP_CONCAT(DISTINCT lr.magento_id SEPARATOR '|') as magento_ids,
//                GROUP_CONCAT(DISTINCT lr.remote_ip SEPARATOR '|') as remote_ips
//            FROM " . $collection->getTable('downloadable/link_purchased') . " dlp
//            INNER JOIN " . $collection->getTable('downloadable/link_purchased_item') . " dlpi ON dlpi.purchased_id = dlp.purchased_id
//            LEFT JOIN " . $collection->getTable('local_manadev/license_extension') . " le ON le.license_verification_no = dlpi.m_license_verification_no
//            LEFT JOIN (
//                SELECT lr.id, lr.magento_id, lr.remote_ip
//                FROM " . $collection->getTable('local_manadev/license_request') . " lr
//                INNER JOIN (
//                    SELECT magento_id, id, MAX(created_at) AS created_at FROM " . $collection->getTable('local_manadev/license_request') . " GROUP BY magento_id
//                ) as tmp ON tmp.id = lr.id
//            ) lr ON lr.id = le.request_id
//            GROUP BY dlp.order_id
//        )")), "`ml`.`order_id` = `main_table`.`entity_id`", array('order_id', 'license_numbers', 'magento_ids', 'remote_ips'));

        $this->setCollection($collection);

        return $this->_basePrepareCollection();
    }

    protected function _basePrepareCollection() {
        if ($this->getCollection()) {

            $this->_preparePage();

            $columnId = $this->getParam($this->getVarNameSort(), $this->_defaultSort);
            $dir      = $this->getParam($this->getVarNameDir(), $this->_defaultDir);
            $filter   = $this->getParam($this->getVarNameFilter(), null);

            if (is_null($filter)) {
                $filter = $this->_defaultFilter;
            }

            if (is_string($filter)) {
                $data = $this->helper('adminhtml')->prepareFilterString($filter);
                $this->_setFilterValues($data);
            }
            else if ($filter && is_array($filter)) {
                $this->_setFilterValues($filter);
            }
            else if(0 !== sizeof($this->_defaultFilter)) {
                $this->_setFilterValues($this->_defaultFilter);
            }

            if (isset($this->_columns[$columnId]) && $this->_columns[$columnId]->getIndex()) {
                $dir = (strtolower($dir)=='desc') ? 'desc' : 'asc';
                $this->_columns[$columnId]->setDir($dir);
                $this->_setCollectionOrder($this->_columns[$columnId]);
            }

            if (!$this->_isExport) {
                $this->getCollection()->load();
                $this->_afterLoadCollection();
            }
        }

        return $this;
    }
}