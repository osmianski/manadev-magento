<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright  Copyright (c) 2006-2016 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml customer grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Local_Manadev_Block_Customer_Grid extends Mage_Adminhtml_Block_Customer_Grid
{

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('customer/customer_collection')
//            ->addNameToSelect()
            ->addAttributeToSelect('email')
            ->addAttributeToSelect('created_at')
            ->addAttributeToSelect('group_id')
            ->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
            ->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
            ->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
            ->joinAttribute('billing_region', 'customer_address/region', 'default_billing', null, 'left')
            ->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left');

        $this->_addNameToSelect($collection);

//        $collection->getSelect()
//        ->joinLeft(array('ml' => new Zend_Db_Expr("(
//            SELECT dlp.customer_id,
//                GROUP_CONCAT(DISTINCT dlpi.m_license_no SEPARATOR '|') as license_numbers,
//                GROUP_CONCAT(DISTINCT dlp.order_increment_id SEPARATOR '|') as order_numbers,
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
//            GROUP BY dlp.customer_id
//
//        )")), "`ml`.`customer_id` = `e`.`entity_id`", array('customer_id', 'license_numbers', 'order_numbers', 'magento_ids', 'remote_ips'));
//
//        $sql = (string)$collection->getSelect();
        $this->setCollection($collection);


        return $this->_basePrepareCollection();
    }

    protected function _addNameToSelect($collection) {
        $fields = array();
        $customerAccount = Mage::getConfig()->getFieldset('customer_account');
        foreach ($customerAccount as $code => $node) {
            if ($node->is('name')) {
                $fields[$code] = $code;
            }
        }

        $adapter = $collection->getConnection();
        $concatenate = array();
        if (isset($fields['prefix'])) {
            $concatenate[] = $adapter->getCheckSql(
                '{{prefix}} IS NOT NULL AND {{prefix}} != \'\'',
                $adapter->getConcatSql(array('LTRIM(RTRIM({{prefix}}))', '\' \'')),
                '\'\'');
        }
        
        if (isset($fields['firstname'])) {
            $concatenate[] = $adapter->getCheckSql(
                '{{firstname}} IS NOT NULL AND {{firstname}} != \'\'',
                $adapter->getConcatSql(array('LTRIM(RTRIM({{firstname}}))', '\' \'')),
                '\'\'');
        }
        
        $concatenate[] = '\' \'';
        if (isset($fields['middlename'])) {
            $concatenate[] = $adapter->getCheckSql(
                '{{middlename}} IS NOT NULL AND {{middlename}} != \'\'',
                $adapter->getConcatSql(array('LTRIM(RTRIM({{middlename}}))', '\' \'')),
                '\'\'');
        }
        
        if (isset($fields['lastname'])) {
            $concatenate[] = $adapter->getCheckSql(
                '{{lastname}} IS NOT NULL AND {{lastname}} != \'\'',
                $adapter->getConcatSql(array('LTRIM(RTRIM({{lastname}}))', '\' \'')),
                '\'\''
            );
        }
        
        if (isset($fields['suffix'])) {
            $concatenate[] = $adapter
                    ->getCheckSql('{{suffix}} IS NOT NULL AND {{suffix}} != \'\'',
                $adapter->getConcatSql(array('\' \'', 'LTRIM(RTRIM({{suffix}}))')),
                '\'\'');
        }

        $nameExpr = $adapter->getConcatSql($concatenate);

        $collection->addExpressionAttributeToSelect('name', $nameExpr, $fields);

        return $this;
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


    protected function _addColumnFilterToCollection($column)
    {
        $fields = array(
            'license_numbers',
            'order_numbers',
            'magento_ids',
            'remote_ips',
        );
        if(in_array($column->getIndex(), $fields)) {
            $field = ($column->getFilterIndex()) ? $column->getFilterIndex() : $column->getIndex();
            $cond = $column->getFilter()->getCondition();
            if($field && isset($cond)) {
                $condition = $this->getCollection()->getConnection()->prepareSqlCondition($field, $cond);
                $this->getCollection()->getSelect()->where($condition);
            }
            return $this;
        } else {
            return parent::_addColumnFilterToCollection($column);
        }
    }

    protected function _prepareColumns()
    {

        $this->addColumnAfter(
            'license_numbers',
            array(
                'header' => $this->__('License Numbers'),
                'index' => 'license_numbers',
                'filter_index' => 'ml.license_numbers',
                'width' => '50',
                'align' => 'center',
                'renderer' => 'local_manadev/adminhtml_renderer_licenseNumbers',
            ),
            'customer_since'
        );

        $this->addColumnAfter(
            'order_numbers',
            array(
                'header' => $this->__('Orders'),
                'index' => 'order_numbers',
                'filter_index' => 'ml.order_numbers',
                'width' => '90',
                'align' => 'center',
                'renderer' => 'local_manadev/adminhtml_renderer_multiline',
            ),
            'license_numbers'
        );

        $this->addColumnAfter(
            'magento_ids',
            array(
                'header' => $this->__('Magento IDs'),
                'index' => 'magento_ids',
                'filter_index' => 'ml.magento_ids',
                'width' => '120',
                'align' => 'center',
                'renderer' => 'local_manadev/adminhtml_renderer_multiline',
            ),
            'order_numbers'
        );

        $this->addColumnAfter(
            'remote_ips',
            array(
                'header' => $this->__('Magento IPs'),
                'index' => 'remote_ips',
                'filter_index' => 'ml.remote_ips',
                'width' => '25',
                'align' => 'center',
                'renderer' => 'local_manadev/adminhtml_renderer_multiline',
            ),
            'magento_ids'
        );

        parent::_prepareColumns();

        $this->removeColumn('Telephone');
        $this->removeColumn('billing_postcode');
        $this->removeColumn('website_id');
        $this->removeColumn('action');
        $this->removeColumn('billing_region');


        return $this;
    }
}
