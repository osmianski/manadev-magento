<?php

/* BASED ON SNIPPET: Resources/Install/upgrade script */
if (defined('COMPILER_INCLUDE_PATH')) {
	throw new Exception(Mage::helper('mana_core')->__('This Magento installation contains pending database installation/upgrade scripts. Please turn off Magento compilation feature while installing/upgrading new modules in Admin Panel menu System->Tools->Compilation.'));
}
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();
$table = $installer->getTable('mana_content/page_tag');
$installer->run("
CREATE TABLE `{$table}` (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  name varchar(255) DEFAULT NULL UNIQUE,
  PRIMARY KEY (id)
)
ENGINE = INNODB DEFAULT CHARSET=utf8;
");
$table = $installer->getTable('mana_content/page_tagRelation');
$installer->run("
CREATE TABLE `{$table }` (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  page_store_id bigint(20) NOT NULL,
  page_tag_id bigint(20) NOT NULL,
  PRIMARY KEY (id),
  KEY page_store_id (page_store_id),
  KEY page_tag_id (page_tag_id),

  CONSTRAINT FK_{$table}_store_id FOREIGN KEY (page_store_id)
  REFERENCES {$this->getTable('mana_content/page_store')} (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT FK_{$table}_tag_id  FOREIGN KEY (page_tag_id)
  REFERENCES {$this->getTable('mana_content/page_tag')} (id) ON DELETE CASCADE ON UPDATE CASCADE
)
ENGINE = INNODB DEFAULT CHARSET=utf8;");
$table = $installer->getTable('mana_content/page_tagSummary');
$installer->run("
CREATE TABLE `{$table }` (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  page_tag_id bigint(20) NOT NULL,
  store_id smallint(5) unsigned NOT NULL,
  popularity int(11) unsigned NOT NULL,
  PRIMARY KEY (id),
  KEY page_tag_id (page_tag_id),
  KEY store_id (store_id),
  UNIQUE INDEX unique_key (page_tag_id, store_id),

  CONSTRAINT FK_{$table}_page_id FOREIGN KEY (page_tag_id)
  REFERENCES {$this->getTable('mana_content/page_tag')} (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_{$table}_store` FOREIGN KEY (`store_id`)
  REFERENCES `{$this->getTable('core/store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
)
ENGINE = INNODB DEFAULT CHARSET=utf8;");
$installer->endSetup();