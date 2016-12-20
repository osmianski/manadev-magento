<?php
/**
 * @category    Mana
 * @package     Mana_Content
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/* BASED ON SNIPPET: Models/Observer */
/**
 * This class observes certain (defined in etc/config.xml) events in the whole system and provides public methods - handlers for
 * these events.
 * @author Mana Team
 *
 */
class Mana_Content_Model_Observer {
	/**
	 * Sets data that will be used on client side AJAX (handles event "m_ajax_options")
	 * @param Varien_Event_Observer $observer
	 */
	public function setAjaxData($observer) {
        /* @var $js Mana_Core_Helper_Js */
        $js = Mage::helper('mana_core/js');
        $params = Mage::app()->getFrontController()->getRequest()->getParams();
        $params['_use_rewrite'] = true;
        $params['_nosid'] = true;
        $url = Mage::getUrl('mana_content/book/view', $params);

        $js
            ->setConfig('mana_content.url', $url)
            ->setConfig('mana_content.ajax.urlKey', Mage::getStoreConfig('mana_content/ajax/url_key_filter'))
            ->setConfig('mana_content.ajax.routeSeparator', Mage::getStoreConfig('mana_content/ajax/route_separator_filter'))
            ->setConfig('mana_content.ajax.containers', Mage::getStoreConfig('mana_content/ajax/containers'));
	}

    /* obsolete handlers. Kept here for easier upgrade */
    public function registerUrl($observer) { }
    public function ajaxCategoryView($observer) {}
    public function ajaxSearchResult($observer) {}
    public function markUpdatableHtml($observer) {}
    public function ajaxCmsIndex($observer) {}
    public function ajaxCmsPage($observer) {}

    public function addToSitemap($observer) {
        $sitemapObject = $observer->getSitemapObject();

        /* @var Mana_Content_Resource_Page_Store_Collection $collection */
        $collection = Mage::getResourceModel("mana_content/page_store_collection");
        $collection->addFieldToFilter('store_id', $sitemapObject->getStoreId());
        $collection->addFieldToFilter('main_table.is_active', 1);
        $collection->addOrder('position', Varien_Data_Collection_Db::SORT_ORDER_ASC);

        $db = $collection->getConnection();
        $schema = $this->seoHelper()->getActiveSchema($sitemapObject->getStoreId());

        $collection->getSelect()
            ->joinInner(array('url' => 'm_seo_url'),
                "`url`.`book_page_id` = `main_table`.`id` AND `url`.`status` = 'active' AND " .
                $db->quoteInto("`url`.`type` = ? AND", 'book_page') .
                $db->quoteInto("`url`.`schema_id` = ?", $schema->getId()), null)
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(new Zend_Db_Expr($db->quoteInto("CONCAT(`url`.`final_url_key`, ?)", '')));

        $baseUrl = Mage::app()->getStore($sitemapObject->getStoreId())->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);

        foreach($db->fetchCol($collection->getSelect()) as $url) {
            $xml = sprintf('<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($baseUrl . $url),
                Mage::getSingleton('core/date')->gmtDate('Y-m-d'),
                'weekly',
                1.0
            );

            $sitemapObject->sitemapFileAddLine($xml);
        }
    }

    public function seoHelper() {
        return Mage::helper('mana_seo');
    }
}