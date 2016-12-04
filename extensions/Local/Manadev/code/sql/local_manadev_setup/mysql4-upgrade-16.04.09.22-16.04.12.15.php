<?php
/* BASED ON SNIPPET: Resources/Install/upgrade script */
/* @var $installer Local_Manadev_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->removeAttribute('catalog_product', 'main_module');

$installer->endSetup();