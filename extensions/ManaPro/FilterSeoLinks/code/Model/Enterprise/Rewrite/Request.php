<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterSeoLinks
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author alin.balan@innobyte.com
 * @author Mana Team
 *
 */
class ManaPro_FilterSeoLinks_Model_Enterprise_Rewrite_Request extends Enterprise_UrlRewrite_Model_Url_Rewrite_Request {
    protected function _processRedirectOptions()
    {
        $isPermanentRedirectOption = $this->_rewrite->hasOption('RP');

        $external = substr($this->_rewrite->getTargetPath(), 0, 6);
        if ($external === 'http:/' || $external === 'https:') {
            $destinationStoreCode = $this->_app->getStore($this->_rewrite->getStoreId())->getCode();
            $this->_setStoreCodeCookie($destinationStoreCode);
            $this->_sendRedirectHeaders($this->_rewrite->getTargetPath(), $isPermanentRedirectOption);
        }

        $targetUrl = $this->_request->getBaseUrl() . '/' . $this->_rewrite->getTargetPath();
	//var_dump(Mage::app()->getRequest()->getParams(), ' hhh');die;

        $storeCode = $this->_app->getStore()->getCode();
        if (Mage::getStoreConfig('web/url/use_store') && !empty($storeCode)) {
            $targetUrl = $this->_request->getBaseUrl() . '/' . $storeCode . '/' . $this->_rewrite->getTargetPath();
        }

        if ($this->_rewrite->hasOption('R') || $isPermanentRedirectOption) {
            $this->_sendRedirectHeaders($targetUrl, $isPermanentRedirectOption);
        }

        $queryString = $this->_getQueryString();
        if ($queryString) {
				if(strpos($targetUrl, '?') !== false) {
						$targetUrl .= '&' . $queryString;
				} else {
						$targetUrl .= '?' . $queryString;
				}
        }

        $this->_request->setRequestUri($targetUrl);
	    //var_dump($targetUrl, Mage::app()->getRequest()->getParams());die;
        $this->_request->setPathInfo($this->_rewrite->getTargetPath());

        return $this;
    }
}
