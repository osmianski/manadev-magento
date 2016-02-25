<?php
/** 
 * @category    Mana
 * @package     Mana_Sitemap
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * Generic helper functions for Mana_Sitemap module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class Mana_Sitemap_Helper_Data extends Mage_Core_Helper_Abstract {
    /**
     * @param Varien_Io_File $io
     * @param string $baseUrl
     * @param int $storeId
     * @param string $date
     */
    public function generateStandardSitemapEntries($io, $baseUrl, $storeId, $date) {
        $this->generateCategorySitemap($io, $baseUrl, $storeId, $date);
        $this->generateProductSitemap($io, $baseUrl, $storeId, $date);
        $this->generateCmsPageSitemap($io, $baseUrl, $storeId, $date);
    }

    /**
     * @param Varien_Io_File $io
     * @param string $baseUrl
     * @param int $storeId
     * @param string $date
     */
    public function generateManadevSitemapEntries($io, $baseUrl, $storeId, $date) {
        $this->generateAttributePageSitemap($io, $baseUrl, $storeId, $date);
        $this->generateOptionPageSitemap($io, $baseUrl, $storeId, $date);
    }

    /**
     * @param Varien_Io_File $io
     * @param string $baseUrl
     * @param int $storeId
     * @param string $date
     */
    public function generateCategorySitemap($io, $baseUrl, $storeId, $date) {
        $changefreq = (string)Mage::getStoreConfig('sitemap/category/changefreq', $storeId);
        $priority   = (string)Mage::getStoreConfig('sitemap/category/priority', $storeId);
        $collection = Mage::getResourceModel('sitemap/catalog_category')->getCollection($storeId);
        foreach ($collection as $item) {
            $xml = sprintf('<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($baseUrl . $item->getUrl()),
                $date,
                $changefreq,
                $priority
            );
            $io->streamWrite($xml);
        }
    }

    /**
     * @param Varien_Io_File $io
     * @param string $baseUrl
     * @param int $storeId
     * @param string $date
     */
    public function generateProductSitemap($io, $baseUrl, $storeId, $date) {
        $changefreq = (string)Mage::getStoreConfig('sitemap/product/changefreq', $storeId);
        $priority   = (string)Mage::getStoreConfig('sitemap/product/priority', $storeId);
        $collection = Mage::getResourceModel('sitemap/catalog_product')->getCollection($storeId);
        foreach ($collection as $item) {
            $xml = sprintf('<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($baseUrl . $item->getUrl()),
                $date,
                $changefreq,
                $priority
            );
            $io->streamWrite($xml);
        }
    }

    /**
     * @param Varien_Io_File $io
     * @param string $baseUrl
     * @param int $storeId
     * @param string $date
     */
    public function generateCmsPageSitemap($io, $baseUrl, $storeId, $date) {
        $changefreq = (string)Mage::getStoreConfig('sitemap/page/changefreq', $storeId);
        $priority   = (string)Mage::getStoreConfig('sitemap/page/priority', $storeId);
        $collection = Mage::getResourceModel('sitemap/cms_page')->getCollection($storeId);
        foreach ($collection as $item) {
            $xml = sprintf('<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($baseUrl . $item->getUrl()),
                $date,
                $changefreq,
                $priority
            );
            $io->streamWrite($xml);
        }
    }

    /**
     * @param Varien_Io_File $io
     * @param string $baseUrl
     * @param int $storeId
     * @param string $date
     */
    public function generateAttributePageSitemap($io, $baseUrl, $storeId, $date) {
        if (!$this->coreHelper()->isManadevAttributePageInstalled()) {
            return;
        }

        $changefreq = (string)Mage::getStoreConfig('sitemap/m_attribute_page/changefreq', $storeId);
        $priority   = (string)Mage::getStoreConfig('sitemap/m_attribute_page/priority', $storeId);
        foreach ($this->attributePageSitemapResource()->getAttributePageUrls($storeId) as $url) {
            $xml = sprintf('<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($baseUrl . $url),
                $date,
                $changefreq,
                $priority
            );
            $io->streamWrite($xml);
        }
    }

    /**
     * @param Varien_Io_File $io
     * @param string $baseUrl
     * @param int $storeId
     * @param string $date
     */
    public function generateOptionPageSitemap($io, $baseUrl, $storeId, $date) {
        if (!$this->coreHelper()->isManadevAttributePageInstalled()) {
            return;
        }

        $changefreq = (string)Mage::getStoreConfig('sitemap/m_option_page/changefreq', $storeId);
        $priority   = (string)Mage::getStoreConfig('sitemap/m_option_page/priority', $storeId);
        foreach ($this->attributePageSitemapResource()->getOptionPageUrls($storeId) as $url) {
            $xml = sprintf('<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($baseUrl . $url),
                $date,
                $changefreq,
                $priority
            );
            $io->streamWrite($xml);
        }
    }

    #region Dependencies

    /**
     * @return Mana_Core_Helper_Data
     */
    public function coreHelper() {
        return Mage::helper('mana_core');
    }

    /**
     * @return Mana_AttributePage_Resource_Sitemap
     */
    public function attributePageSitemapResource() {
        return Mage::getResourceSingleton('mana_attributepage/sitemap');
    }
    #endregion
}