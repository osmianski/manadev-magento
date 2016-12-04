<?php
/* BASED ON SNIPPET: Resources/Install/upgrade script */
/* @var $installer Local_Manadev_Resource_Setup */
$installer = $this;

$installer->startSetup();
$table = "m_domain_history";
$installer->run("
    DELETE FROM {$table} WHERE m_registered_domain = '' AND m_store_info = ''
");
$installer->endSetup();