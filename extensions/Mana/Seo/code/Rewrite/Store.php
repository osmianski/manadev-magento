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
    /**
     * This method is used to generate URL for store switcher (or language switcher) blocks, typically located in page header
     * or page footer. `$this` is store to which the URL should switch our user, whereas `Mage::app()->getRequest()` represents
     * current user location in current store.
     * @param bool $fromStore
     * @return string
     */
    public function getCurrentUrl($fromStore = true) {
        // we only change generation of store URLs on layered navigation enabled pages
        if ($parsedUrl = Mage::registry('m_parsed_url')) {
            // get instance of a model which can generate URL for target store
            $urlModel = Mage::getModel('core/url');
            $urlModel->setStore($this);

            // set basic URL generation parameters
            $params = array_merge(array(
                '_use_rewrite' => true,
                '_secure' => Mage::app()->getFrontController()->getRequest()->isSecure(),
            ), $parsedUrl->getImplodedParameters());

            // add URL query parameters (all applied filters, toolbar parameters and other arbitrary parameters)
            $query = count($parsedUrl->getQueryParameters())
                ? $parsedUrl->getImplodedQueryParameters()
                : array();

            // get array of URL query parameters applied to the current page. The resulting array may contain applied filters in non-
            // SEO-friendly form (added by Mana_Seo_Router) like [ "price" => "0,100" ] and other arbitrary parameters like
            // [ "qq" => "pp" ].
            $currQuery = Mage::app()->getRequest()->getQuery();

            // get base URL of this store
            $storeUrl = Mage::app()->getStore()->isCurrentlySecure()
                    ? $this->getUrl('', array('_secure' => true))
                    : $this->getUrl('');

            // retrieve name of session id URL query parameter, typically "SID"
            $sidQueryParam = $this->_getSession()->getSessionIdQueryParam();

            // in case session id is in URL and not in cookie we may remove it from current URL query parameters so, that would
            // basically start new session after store is switched
            if (!empty($currQuery[$sidQueryParam])) {
                if ($this->_getSession()->getSessionIdForHost($storeUrl) != $currQuery[$sidQueryParam]) {
                    $params['_nosid'] = true;
                }
            }

            // add source and target store to target URL query
            $fromStoreCode = $fromStore === true ? Mage::app()->getStore()->getCode() : $fromStore;
            if ($this->getCode() != $fromStoreCode) {
                if (!Mage::getStoreConfigFlag(Mage_Core_Model_Store::XML_PATH_STORE_IN_URL, $this->getCode())) {
                    $query['___store'] = $this->getCode();
                }
                $query['___from_store'] = $fromStore === true ? Mage::app()->getStore()->getCode() : $fromStore;
            }

            // add URL query parameters (if any) to URL generation parameters
            if (count($query)) {
                $params['_query'] = $query;
            }

            // generate and return URL for target store
            $url = $urlModel->getUrl($parsedUrl->getRoute(), $params);
            return $url;
        }
        else {
            return parent::getCurrentUrl($fromStore);
        }
	}
}