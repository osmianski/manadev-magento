<?php
/**
 * @category    Mana
 * @package     Local_Manadev
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

class Local_Manadev_Resource_Accounting extends Mage_Core_Model_Mysql4_Abstract {
	protected function _construct() {
        $this->_init('sales/invoice_grid', 'entity_id');
	}   
	/**
	 * @param Varien_Db_Select $select
	 */
	public function calculateInvoiceAmounts($select) {
		$select = clone $select;
		foreach ($this->getReadConnection()->fetchAll($select->where('main_table.m_exchange_rate IS NULL')) as $row) {
			$date = new DateTime($row['created_at']);
			$date = $date->format('Y-m-d');
			
			$serverDate = Mage::app()->getLocale()->date($row['created_at'], Varien_Date::DATETIME_INTERNAL_FORMAT)->toString('Y-MM-d');
			if ($serverDate > '2011-07-31') {
				$date = $serverDate;
			}
			
			$values = array();
			$values['m_exchange_rate'] = $this->_getRate($row['order_currency_code'], $date);
			$values['m_total'] = round($row['grand_total'] * $values['m_exchange_rate'] / 1.21, 2);
			$values['m_vat'] = round($values['m_total'] * 0.21, 2);
			$values['m_grand_total'] = $values['m_total'] + $values['m_vat'];
			$this->_getWriteAdapter()->update('sales_flat_invoice_grid', $values, 'entity_id = '.$row['entity_id']);
		}
		return $this;
	}
	public function calculateCreditMemoAmounts($select) {
		$select = clone $select;
		foreach ($this->getReadConnection()->fetchAll($select->where('main_table.m_exchange_rate IS NULL')) as $row) {
			$date = new DateTime($row['created_at']);
			$date = $date->format('Y-m-d');
			
			$serverDate = Mage::app()->getLocale()->date($row['created_at'], Varien_Date::DATETIME_INTERNAL_FORMAT)->toString('Y-MM-d');
			if ($serverDate > '2011-07-31') {
				$date = $serverDate;
			}
			
			$values = array();
			$values['m_exchange_rate'] = $this->_getRate($row['order_currency_code'], $date);
			if ($serverDate > '2011-09-12') {
				$values['m_total'] = round($row['grand_total'] * $values['m_exchange_rate'], 2);
				$values['m_vat'] = 0;
			}
			else {
				$values['m_total'] = round($row['grand_total'] * $values['m_exchange_rate'] / 1.21, 2);
				$values['m_vat'] = round($values['m_total'] * 0.21, 2);
			}
			$values['m_grand_total'] = $values['m_total'] + $values['m_vat'];
			$this->_getWriteAdapter()->update('sales_flat_creditmemo_grid', $values, 'entity_id = '.$row['entity_id']);
		}
		return $this;
	}
	protected function _getRate($currencyCode, $date) {
		$url = "http://webservices.lb.lt/ExchangeRates/ExchangeRates.asmx/getExchangeRate?Currency=$currencyCode&Date=$date";
		$xml = simplexml_load_string(file_get_contents($url));
		if ($xml) {
			return (float)(string) $xml;
		}
		else {
			throw new Exception('Lietuvos Bankas neatsako!');
		}
	}
}