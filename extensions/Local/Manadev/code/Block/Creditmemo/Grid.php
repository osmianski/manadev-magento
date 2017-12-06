<?php
/**
 * @category    Mana
 * @package     Local_Manadev
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */


class Local_Manadev_Block_Creditmemo_Grid extends Mage_Adminhtml_Block_Sales_Creditmemo_Grid {
    protected function _prepareColumns()
    {
        $this->addColumn('m_sales_account', array(
            'header' => Mage::helper('sales')->__('Bookkeeping Account'),
            'index' => 'm_sales_account',
        ));

        $this->addColumn('m_is_business', array(
            'header' => Mage::helper('sales')->__('Business Name'),
            'index' => 'm_is_business',
        ));

        $this->addColumn('increment_id', array(
            'header'    => Mage::helper('sales')->__('Credit Memo #'),
            'index'     => 'increment_id',
            'type'      => 'text',
        ));

        $this->addColumn('created_at', array(
            'header'    => Mage::helper('sales')->__('Created At'),
            'index'     => 'created_at',
            'type'      => 'datetime',
        	'format'	=> 'y.MM.dd',
        ));

        $this->addColumn('order_increment_id', array(
            'header'    => Mage::helper('sales')->__('Order #'),
            'index'     => 'order_increment_id',
            'type'      => 'text',
        ));

        $this->addColumn('billing_name', array(
            'header' => Mage::helper('sales')->__('Bill to Name'),
            'index' => 'billing_name',
        ));

        $this->addColumn('grand_total', array(
            'header'    => Mage::helper('customer')->__('Refunded'),
            'index'     => 'grand_total',
            'type'      => 'number',
            'align'     => 'right',
        ));

        $this->addColumn('currency', array(
            'header'    => Mage::helper('customer')->__('Currency'),
            'index'     => 'order_currency_code',
            'type'      => 'text',
            'align'     => 'center',
        ));
        $this->addColumn('m_exchange_rate', array(
            'header'    => Mage::helper('customer')->__('Exchange Rate'),
            'index'     => 'm_exchange_rate',
            'type'      => 'number',
            'align'     => 'center',
        ));
        $this->addColumn('m_grand_total', array(
            'header'    => Mage::helper('customer')->__('Suma LTL su PVM'),
            'index'     => 'm_grand_total',
            'type'      => 'number',
            'align'     => 'right',
        ));
        $this->addColumn('m_total', array(
            'header'    => Mage::helper('customer')->__('Suma LTL be PVM'),
            'index'     => 'm_total',
            'type'      => 'number',
            'align'     => 'right',
        ));
        $this->addColumn('m_vat', array(
            'header'    => Mage::helper('customer')->__('PVM Suma'),
            'index'     => 'm_vat',
            'type'      => 'number',
            'align'     => 'right',
        ));

        $this->addColumn('m_country', array(
            'header' => Mage::helper('sales')->__('Country'),
            'index' => 'm_country',
        ));


//        $this->addColumn(
//            'license_numbers',
//            array(
//                'header' => $this->__('License Numbers'),
//                'index' => 'license_numbers',
//                'filter_index' => 'ml.license_numbers',
//                'width' => '50',
//                'align' => 'center',
//                'renderer' => 'local_manadev/adminhtml_renderer_multiline',
//            )
//        );
//
//        $this->addColumn(
//            'magento_ids',
//            array(
//                'header' => $this->__('Magento IDs'),
//                'index' => 'magento_ids',
//                'filter_index' => 'ml.magento_ids',
//                'width' => '120',
//                'align' => 'center',
//                'renderer' => 'local_manadev/adminhtml_renderer_multiline',
//            )
//        );
//
//        $this->addColumn(
//            'remote_ips',
//            array(
//                'header' => $this->__('Magento IPs'),
//                'index' => 'remote_ips',
//                'filter_index' => 'ml.remote_ips',
//                'width' => '50',
//                'align' => 'center',
//                'renderer' => 'local_manadev/adminhtml_renderer_multiline',
//            )
//        );

        $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));

        return $this;
    }
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('creditmemo_ids');
        $this->getMassactionBlock()->setUseSelectAll(false);

        $this->getMassactionBlock()->addItem('pdfcreditmemos_order_en', array(
             'label'=> Mage::helper('sales')->__('PDF Credit Memos (EN)'),
             'url'  => $this->getUrl('*/sales_creditmemo/pdfcreditmemosEn'),
        ));
    	
        $this->getMassactionBlock()->addItem('pdfcreditmemos_order_lt', array(
             'label'=> Mage::helper('sales')->__('PDF Credit Memos (LT)'),
             'url'  => $this->getUrl('*/sales_creditmemo/pdfcreditmemosLt'),
        ));

        if (Mage::getStoreConfigFlag('local_manadev/accounting/recalculate')) {
            $this->getMassactionBlock()->addItem('prepare_for_accounting', array(
                 'label'=> Mage::helper('sales')->__('Prepare for Accounting'),
                 'url'  => $this->getUrl('*/sales_creditmemo/prepareForAccounting'),
            ));

            $this->getMassactionBlock()->addItem('recalculate', array(
                'label' => Mage::helper('sales')->__('Recalculate'),
                'url' => $this->getUrl('*/sales_creditmemo/recalculate'),
            ));
        }
        if (Mage::getStoreConfigFlag('local_manadev/accounting/delete_creditmemo')) {
            $this->getMassactionBlock()->addItem('delete', array(
                'label' => Mage::helper('sales')->__('Delete'),
                'url' => $this->getUrl('*/sales_creditmemo/delete'),
            ));
        }
        return $this;
    }

    protected function _prepareCollection() {
        $collection = Mage::getResourceModel($this->_getCollectionClass());

//        $collection->getSelect()
//            ->joinLeft(array('ml' => new Zend_Db_Expr("(
//                SELECT dlp.order_id,
//                    GROUP_CONCAT(DISTINCT dlpi.m_license_no SEPARATOR '|') as license_numbers,
//                    GROUP_CONCAT(DISTINCT lr.magento_id SEPARATOR '|') as magento_ids,
//                    GROUP_CONCAT(DISTINCT lr.remote_ip SEPARATOR '|') as remote_ips
//                FROM " . $collection->getTable('downloadable/link_purchased') . " dlp
//                INNER JOIN " . $collection->getTable('downloadable/link_purchased_item') . " dlpi ON dlpi.purchased_id = dlp.purchased_id
//                LEFT JOIN " . $collection->getTable('local_manadev/license_extension') . " le ON le.license_verification_no = dlpi.m_license_verification_no
//                LEFT JOIN (
//                    SELECT lr.id, lr.magento_id, lr.remote_ip
//                    FROM " . $collection->getTable('local_manadev/license_request') . " lr
//                    INNER JOIN (
//                        SELECT magento_id, id, MAX(created_at) AS created_at FROM " . $collection->getTable('local_manadev/license_request') . " GROUP BY magento_id
//                    ) as tmp ON tmp.id = lr.id
//                ) lr ON lr.id = le.request_id
//                GROUP BY dlp.order_id
//
//            )")), "`ml`.`order_id` = `main_table`.`order_id`", array('order_id', 'license_numbers', 'magento_ids', 'remote_ips'));


        $this->setCollection($collection);
        return $this->_basePrepareCollection();
    }

    protected function _basePrepareCollection() {
        if ($this->getCollection()) {

            $this->_preparePage();

            $columnId = $this->getParam($this->getVarNameSort(), $this->_defaultSort);
            $dir = $this->getParam($this->getVarNameDir(), $this->_defaultDir);
            $filter = $this->getParam($this->getVarNameFilter(), null);

            if (is_null($filter)) {
                $filter = $this->_defaultFilter;
            }

            if (is_string($filter)) {
                $data = $this->helper('adminhtml')->prepareFilterString($filter);
                $this->_setFilterValues($data);
            } else {
                if ($filter && is_array($filter)) {
                    $this->_setFilterValues($filter);
                } else {
                    if (0 !== sizeof($this->_defaultFilter)) {
                        $this->_setFilterValues($this->_defaultFilter);
                    }
                }
            }

            if (isset($this->_columns[$columnId]) && $this->_columns[$columnId]->getIndex()) {
                $dir = (strtolower($dir) == 'desc') ? 'desc' : 'asc';
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