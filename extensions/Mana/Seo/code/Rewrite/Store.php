<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterSeoLinks
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Rewrite of Mage_Core_Model_Store which makes store links SEO firendly
 * @author Mana Team
 *
 */
class Mana_Seo_Rewrite_Store extends Mage_Core_Model_Store {
	public function getCurrentUrl($fromStore = true) {
        $sidQueryParam = $this->_getSession()->getSessionIdQueryParam();

        $storeUrl = Mage::app()->getStore()->isCurrentlySecure()
                ? $this->getUrl('', array('_secure' => true))
                : $this->getUrl('');
        $storeParsedUrl = parse_url($storeUrl);

        $params = array(
            '_current' => true,
            '_m_escape' => '',
            '_use_rewrite' => true,
            '_secure' => Mage::app()->getFrontController()->getRequest()->isSecure(),
//            '_query' => array(
//                '___from_store' => null,
//                '___store' => null,
//            ),
        );
        $currentUrl = Mage::getUrl('*/*/*', $params);
        if (($pos = strpos($currentUrl, '?')) !== false) {
            $currentUrl = substr($currentUrl, 0, $pos);
        }
        $requestString = substr($currentUrl, strlen(
                $storeParsedUrl['scheme'] . '://' . $storeParsedUrl['host']
                . (isset($storeParsedUrl['port']) ? ':' . $storeParsedUrl['port'] : '')
                . $storeParsedUrl['path']));

        $storeParsedQuery = array();
        if (isset($storeParsedUrl['query'])) {
            parse_str($storeParsedUrl['query'], $storeParsedQuery);
        }

        $currQuery = Mage::app()->getRequest()->getQuery();
        if (isset($currQuery[$sidQueryParam]) && !empty($currQuery[$sidQueryParam])
            && $this->_getSession()->getSessionIdForHost($storeUrl) != $currQuery[$sidQueryParam]
        ) {
            unset($currQuery[$sidQueryParam]);
        }

        foreach ($currQuery as $k => $v) {
            $storeParsedQuery[$k] = $v;
        }

        if (!Mage::getStoreConfigFlag(Mage_Core_Model_Store::XML_PATH_STORE_IN_URL, $this->getCode())) {
            $storeParsedQuery['___store'] = $this->getCode();
        }
        //if ($fromStore !== false) {
            $storeParsedQuery['___from_store'] = $fromStore === true ? Mage::app()->getStore()->getCode() : $fromStore;
        //}

        return $storeParsedUrl['scheme'] . '://' . $storeParsedUrl['host']
            . (isset($storeParsedUrl['port']) ? ':' . $storeParsedUrl['port'] : '')
            . $storeParsedUrl['path'] . $requestString
            . ($storeParsedQuery ? '?'.http_build_query($storeParsedQuery, '', '&amp;') : '');



        return Mage::helper('mana_filters')->markLayeredNavigationUrl(Mage::getUrl('*/*/*', $params), '*/*/*', $params);
        /* @var $url Mana_Seo_Rewrite_Url */
        $url = Mage::getSingleton('core/url');
		return $url->getUrl('*/*/*', parent::getCurrentUrl($fromStore));
	}
}