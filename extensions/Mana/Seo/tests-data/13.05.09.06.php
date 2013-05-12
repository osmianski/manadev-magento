<?php
/**
 * @author Mana Team
 */

/* @var $this Mana_Core_Test_Setup */

/* @var $utils Mana_Core_Helper_Utils */
$utils = Mage::helper('mana_core/utils');

/* @var $db Mana_Db_Helper_Data */
$db = Mage::helper('mana_db');

/* @var $history Mana_Seo_Model_UrlHistory */

switch ($this->getTestVariation()) {
    case 'test':
        $utils->setStoreConfig('catalog/seo/category_url_suffix', '.html');

        $history = $db->getModel('mana_seo/urlHistory');
        $history
            ->setUrlKey('.htm')
            ->setRedirectTo('.html')
            ->setType(Mana_Seo_Model_UrlHistory::TYPE_CATEGORY_SUFFIX)
            ->save();

        $history = $db->getModel('mana_seo/urlHistory');
        $history
            ->setUrlKey('')
            ->setRedirectTo('.htm')
            ->setType(Mana_Seo_Model_UrlHistory::TYPE_CATEGORY_SUFFIX)
            ->save();
        break;

    case 'test2':
        $utils->setStoreConfig('catalog/seo/category_url_suffix', '');

        $history = $db->getModel('mana_seo/urlHistory');
        $history
            ->setUrlKey('.htm')
            ->setRedirectTo('')
            ->setType(Mana_Seo_Model_UrlHistory::TYPE_CATEGORY_SUFFIX)
            ->save();

        $history = $db->getModel('mana_seo/urlHistory');
        $history
            ->setUrlKey('.html')
            ->setRedirectTo('.htm')
            ->setType(Mana_Seo_Model_UrlHistory::TYPE_CATEGORY_SUFFIX)
            ->save();
        break;
}

$utils->reindex('mana_seo');
