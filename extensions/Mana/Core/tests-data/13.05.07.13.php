<?php
/**
 * @author Mana Team
 */

/* @var $category Mage_Catalog_Model_Category */
$category = Mage::getModel('catalog/category');
/* @noinspection PhpUndefinedMethodInspection */
$category
    ->setStoreId(0)
    ->loadByAttribute('url_key', 'electronics')
    ->setIsAnchor(1)
    ->save();
