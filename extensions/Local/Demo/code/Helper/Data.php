<?php
/**
 * @category    Mana
 * @package     Local_Demo
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/* BASED ON SNIPPET: New Module/Helper/Data.php */
/**
 * Generic helper functions for Local_Demo module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class Local_Demo_Helper_Data extends Mage_Core_Helper_Abstract {
	public function product($keys) {
		try {
			$result = null;
			$settingsXmlFilename = realpath(str_replace('/', DS, Mage::getStoreConfig('local_demo/main_site/path'))).
				DS.'app'.DS.'etc'.DS.'local.xml';
			if (!file_exists($settingsXmlFilename)) {
				throw new Exception($this->__('File %s does not exist.', $settingsXmlFilename));
			}
			$settingsXml = simplexml_load_string(file_get_contents($settingsXmlFilename));
			$dbXml = $settingsXml->global->resources->default_setup->connection;
			/* @var $db mysqli */ $db = new mysqli((string)$dbXml->host, (string)$dbXml->username, (string)$dbXml->password, (string)$dbXml->dbname);
			try {
				if ($id = Mage::getStoreConfig('local_demo/product/id')) {
					if (/* @var $result MySQLi_Result */ $result = $db->query("
						SELECT e.entity_id, e.name, e.demo_description AS short_description, e.url_key, 
							COALESCE(e.special_price, e.price) AS final_price,
							IF(e.price > 0, ROUND((1 - COALESCE(e.special_price, e.price) / e.price) * 100), 0) AS discount_percent
						FROM catalog_product_flat_1 AS e
						WHERE entity_id = $id 
						"))
					{
						if (!($row = $result->fetch_row())) {
							throw new Exception($this->__('Product %s not found.', $id));
						}
						$result->free();
					}
					else {
						throw new Exception($this->__('Product %s query failed.', $id));
					}
				}
				else {
					throw new Exception($this->__('Please specify product id in configuration.'));
				}
			}
			catch (Exception $e) {
				$db->close();
				throw $e;
			}
			$db->close();
			return array_combine($keys, $row);
		}
		catch (Exception $e) {
			Mage::logException($e);
			return array_combine($keys, array_fill(0, count($keys), ''));	
		}
	
	}
}