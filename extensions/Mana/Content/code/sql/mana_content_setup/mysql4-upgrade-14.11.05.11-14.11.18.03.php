<?php

/* BASED ON SNIPPET: Resources/Install/upgrade script */
if (defined('COMPILER_INCLUDE_PATH')) {
	throw new Exception(Mage::helper('mana_core')->__('This Magento installation contains pending database installation/upgrade scripts. Please turn off Magento compilation feature while installing/upgrading new modules in Admin Panel menu System->Tools->Compilation.'));
}
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();
$table = $installer->getTable('mana_content/page_relatedProduct');
$installer->run("
    CREATE TABLE `$table` (
      `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
      `page_global_id` BIGINT(20) NOT NULL,
      `product_id` int(10) UNSIGNED NOT NULL,
      `edit_massaction` tinyint NOT NULL default '0',
      PRIMARY KEY (id),
      INDEX page_global_id (page_global_id),
      INDEX product_id (product_id),
      CONSTRAINT FK_{$table}_global_id FOREIGN KEY (page_global_id)
      REFERENCES {$this->getTable('mana_content/page_global')} (id) ON DELETE CASCADE ON UPDATE CASCADE,
      CONSTRAINT FK_{$table}_product_id  FOREIGN KEY (product_id)
      REFERENCES {$this->getTable('catalog/product')} (entity_id) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE = INNODB DEFAULT CHARSET=utf8;
");
$installer->endSetup();

