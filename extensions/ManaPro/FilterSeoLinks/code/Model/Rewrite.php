<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterSeoLinks
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * Extends standard Magento SEO techniques in layered navigation urls. 
 * Standard Magento parses "apparel/shoes.html?shoe_type=52&shoe_type-show-all=1" into 
 * "catalog/category/view/id/5?shoe_type=52&shoe_type-sh". For layered navigation we go further and parse
 * "apparel/shoes/where/shoe-type/golf-shoes/shoe-type-show-all.html" into the same original URL. 
 * @author Mana Team
 *
 */
class ManaPro_FilterSeoLinks_Model_Rewrite extends Mage_Core_Model_Url_Rewrite {
    public function _construct() {
    	parent::_construct();
    	$this->_resourceName = 'manapro_filterseolinks/rewrite';
    }
    /** 
     * Implements database-centered URL rewrite logic (translates human friendly URLs into standard 
     * module/controller/action/params form).
     * 
     * @see Mage_Core_Model_Url_Rewrite::rewrite()
     * 
     * This method is based on Mage_Core_Model_Url_Rewrite::rewrite() source. Modifications are marked with comments.
     */
    public function rewrite(Zend_Controller_Request_Http $request=null, Zend_Controller_Response_Http $response=null)
    {
        /* @var $_core Mana_Core_Helper_Data */ $_core = Mage::helper(strtolower('Mana_Core'));
        $conditionalWord = $_core->getStoreConfig('mana_filters/seo/conditional_word');
        $categorySuffix = Mage::helper('manapro_filterseolinks')->getCategoryUrlSuffix();

        if (!Mage::isInstalled()) {
            return false;
        }
        if (is_null($request)) {
            $request = Mage::app()->getFrontController()->getRequest();
        }
        if (is_null($response)) {
            $response = Mage::app()->getFrontController()->getResponse();
        }
        if (is_null($this->getStoreId()) || false===$this->getStoreId()) {
            $this->setStoreId(Mage::app()->getStore()->getId());
        }

        /**
         * We have two cases of incoming paths - with and without slashes at the end ("/somepath/" and "/somepath").
         * Each of them matches two url rewrite request paths - with and without slashes at the end ("/somepath/" and "/somepath").
         * Choose any matched rewrite, but in priority order that depends on same presence of slash and query params.
         */
        $requestCases = array();
        $pathInfo = urldecode($request->getPathInfo());
        $origSlash = (substr($pathInfo, -1) == '/') ? '/' : '';
        $requestPath = trim($pathInfo, '/');

        if ($conditionalWord && (($conditionPos = strpos($requestPath, $conditionalWord.'/')) === 0)) {
            Varien_Autoload::registerScope('catalog');
            Varien_Autoload::registerScope('cms');
            $pageId = Mage::getStoreConfig(Mage_Cms_Helper_Page::XML_PATH_HOME_PAGE);
            $this->_setPageCategoryId($pageId);
            $parameters = substr($requestPath, $conditionPos + strlen($conditionalWord.'/'));
            $_SERVER['QUERY_STRING'] = http_build_query($this->_getQueryParameters($parameters), '', '&');
            $request->setAlias(self::REWRITE_REQUEST_PATH_ALIAS, $this->getRequestPath());
            if (Mage::getStoreConfig('web/url/use_store') && $storeCode = Mage::app()->getStore()->getCode()) {
                $targetUrl = $request->getBaseUrl(). '/' . $storeCode;
            }
            else {
                $targetUrl = $request->getBaseUrl();
            }
            if ($queryString = $this->_getQueryString()) {
                if (strrpos($targetUrl, '/') !== strlen($targetUrl) - 1) {
                    $targetUrl .= '/';
                }
                $targetUrl .= '?'.$queryString;
            }

            if ($parameters) {
                $request->setRequestUri($request->getBaseUrl() . str_replace('/' . $conditionalWord . '/' . $parameters, '', $request->getRequestString()));
                $request->setPathInfo();
            }
            $request->setRequestUri($targetUrl);
            $request->setPathInfo('');
            return true;
        }
        elseif ($conditionalWord && (($conditionPos = strpos($requestPath, '/'.$conditionalWord.'/')) != false)) {
            Varien_Autoload::registerScope('catalog');
            Varien_Autoload::registerScope('cms');
            $cmsPath = substr($requestPath, 0, $conditionPos);
            $page   = Mage::getModel('cms/page');
            $pageId = $page->checkIdentifier($cmsPath, Mage::app()->getStore()->getId());
            if ($pageId) {
                $this->_setPageCategoryId($pageId);
                $parameters = substr($requestPath, $conditionPos + strlen('/'.$conditionalWord.'/'));
                $_SERVER['QUERY_STRING'] = http_build_query($this->_getQueryParameters($parameters), '', '&');
                $request->setAlias(self::REWRITE_REQUEST_PATH_ALIAS, $this->getRequestPath());
                if (Mage::getStoreConfig('web/url/use_store') && $storeCode = Mage::app()->getStore()->getCode()) {
                    $targetUrl = $request->getBaseUrl(). '/' . $storeCode.'/'.$cmsPath;
                }
                else {
                    $targetUrl = $request->getBaseUrl().'/'.$cmsPath;
                }
                if ($queryString = $this->_getQueryString()) {
                    $targetUrl .= '?'.$queryString;
                }

                if ($parameters) {
                    $request->setRequestUri($request->getBaseUrl() . str_replace('/' . $conditionalWord . '/' . $parameters, '', $request->getRequestString()));
                    $request->setPathInfo();
                }
                $request->setRequestUri($targetUrl);
                $request->setPathInfo($cmsPath);
                return true;
            }
        }

        $altSlash = $origSlash ? '' : '/'; // If there were final slash - add nothing to less priority paths. And vice versa.
        $queryString = $this->_getQueryString(); // Query params in request, matching "path + query" has more priority
        if ($queryString) {
            $requestCases[] = $requestPath . $origSlash . '?' . $queryString;
            $requestCases[] = $requestPath . $altSlash . '?' . $queryString;
        }
        $requestCases[] = $requestPath . $origSlash;
        $requestCases[] = $requestPath . $altSlash;

        // MANA BEGIN: in case we have specially named segment in URL suspect it is layered navigation url. Then 
        // add additional checks to $requestCases
        if ($conditionalWord && (($conditionPos = strpos($requestPath, '/'.$conditionalWord.'/')) != false) &&
        	(!$categorySuffix || $categorySuffix == '/' || strrpos($requestPath, $categorySuffix) == strlen($requestPath) - strlen($categorySuffix))) 
        {
            Varien_Autoload::registerScope('catalog');
        	$layeredPath = substr($requestPath, 0, $conditionPos) . ($categorySuffix != '/' ? $categorySuffix : '');
	        $requestCases[] = $layeredPath . $origSlash;
	        $requestCases[] = $layeredPath . $altSlash;
        }
        else $layeredPath = false;
        // MANA END
        
        $this->loadByRequestPath($requestCases);

        /**
         * Try to find rewrite by request path at first, if no luck - try to find by id_path
         */
        if (!$this->getId() && isset($_GET['___from_store'])) {
            try {
                $fromStoreId = Mage::app()->getStore($_GET['___from_store'])->getId();
            }
            catch (Exception $e) {
                return false;
            }

            $this->setStoreId($fromStoreId)->loadByRequestPath($requestCases);
            if (!$this->getId()) {
                return false;
            }
            $this->setStoreId(Mage::app()->getStore()->getId())->loadByIdPath($this->getIdPath());
        }

        if (!$this->getId()) {
            return false;
        }

		// MANA BEGIN: in case we found rewrite based on filtered navigation url, do some magic passes
		// around request object and $_SERVER['QUERY_STRING'] to make it look like 
		// "apparel/shoes.html?shoe_type=52&shoe_type-show-all=1"
		$parameters = '';
		if ($layeredPath !== false && in_array($this->getRequestPath(), array($layeredPath . $origSlash, $layeredPath . $altSlash))
			&& $this->getCategoryId()) {
			if ($categorySuffix && $categorySuffix != '/') {
				$parameters = substr($requestPath, $conditionPos + strlen('/'.$conditionalWord.'/'), 
					strlen($requestPath) - strlen($categorySuffix) - $conditionPos - strlen('/'.$conditionalWord.'/'));
			}
			else {
				$parameters = substr($requestPath, $conditionPos + strlen('/'.$conditionalWord.'/'));
			}
			$_SERVER['QUERY_STRING'] = http_build_query($this->_getQueryParameters($parameters), '', '&');
		}
        // MANA END
        
        $request->setAlias(self::REWRITE_REQUEST_PATH_ALIAS, $this->getRequestPath());
        $external = substr($this->getTargetPath(), 0, 6);
        $isPermanentRedirectOption = $this->hasOption('RP');
        if ($external === 'http:/' || $external === 'https:') {
            if ($isPermanentRedirectOption) {
                header('HTTP/1.1 301 Moved Permanently');
            }
            header("Location: ".$this->getTargetPath());
            exit;
        } else {
            $targetUrl = $request->getBaseUrl(). '/' . $this->getTargetPath();
        }
        $isRedirectOption = $this->hasOption('R');
        if ($isRedirectOption || $isPermanentRedirectOption) {
            if (Mage::getStoreConfig('web/url/use_store') && $storeCode = Mage::app()->getStore()->getCode()) {
                $targetUrl = $request->getBaseUrl(). '/' . $storeCode . '/' .$this->getTargetPath();
            }
            if ($isPermanentRedirectOption) {
                header('HTTP/1.1 301 Moved Permanently');
            }
            header('Location: '.$targetUrl);
            exit;
        }

        if (Mage::getStoreConfig('web/url/use_store') && $storeCode = Mage::app()->getStore()->getCode()) {
                $targetUrl = $request->getBaseUrl(). '/' . $storeCode . '/' .$this->getTargetPath();
            }

        $queryString = $this->_getQueryString();
        if ($queryString) {
            $targetUrl .= '?'.$queryString;
        }

        if ($parameters) {
            $request->setRequestUri($request->getBaseUrl().str_replace('/' . $conditionalWord . '/' . $parameters, '', $request->getRequestString()));
            $request->setPathInfo();
        }
        $request->setRequestUri($targetUrl);
        $request->setPathInfo($this->getTargetPath());
        $request->setActionName('');

        return true;
    }
    
    protected function _getQueryParameters($parameters) {
    	$parameters = explode('/', $parameters);
    	$state = 0; // waiting for parameter
    	$urlValue = '';
    	$filterName = false;
    	$result = array();
        
        /* @var $_core Mana_Core_Helper_Data */ $_core = Mage::helper(strtolower('Mana_Core'));
		$showAllSuffix = $_core->getStoreConfig('mana_filters/seo/show_all_suffix');
		$showAllSeoSuffix = $showAllSuffix;

    	for ($parameterIndex = 0; $parameterIndex < count($parameters); ) {
    	    $parameter = $parameters[$parameterIndex];
    	    switch ($state) {
                case 0: // waiting for parameter
                    if ($showAllSuffix && strrpos($parameter, $showAllSeoSuffix) === strlen($parameter) - strlen($showAllSeoSuffix)) {
                        $state = 2; // show all suffix found
                    }
                    elseif ($filterName = $this->_getFilterName($parameter)) {
                        $urlValue = '';
                        $parameterIndex++;
                        $state = 1; // waiting for value
                    }
                    else {
                        $parameterIndex+=2;
                    }
                    break;

                case 1: // waiting for value
                    if (!$urlValue) {
                        $urlValue = $parameter;
                        $parameterIndex++;
                    }
                    elseif ($nextFilterName = $this->_getFilterName($parameter)) {
                        $values = array();
                        foreach (explode('_', $urlValue) as $value) {
                            $values[] = $this->_getFilterValue($filterName, $value);
                        }
                        $result[$filterName] = implode('_', $values);

                        $filterName = $nextFilterName;
                        $urlValue = '';
                        $parameterIndex++;
                    }
                    else {
                        $urlValue .= '/'.$parameter;
                        $parameterIndex++;
                    }
                    break;
                case 2: // show all suffix found
                    $requestVar = substr($parameter, 0, strlen($parameter) - strlen($showAllSeoSuffix));
                    $filterName = $this->_getFilterName($requestVar);
                    $result[$filterName . $showAllSuffix] = 1;

                    $parameterIndex++;
                    break;
            }
    	}
    	if ($state == 1 && $urlValue) {
            $values = array();
            foreach (explode('_', $urlValue) as $value) {
                $values[] = $this->_getFilterValue($filterName, $value);
            }
            $result[$filterName] = implode('_', $values);
        }
    	return $result;
    }
    protected function _getFilterName($seoName) {
		$vars = Mage::helper('manapro_filterseolinks')->getRewriteVars();
        if ($seoName == Mage::helper('manapro_filterseolinks')->getCategoryName()) {
    		return 'cat'; 
    	}
    	elseif(isset($vars[$seoName])) {
    		return $vars[$seoName];
    	}
    	else {
        	/* @var $_core Mana_Core_Helper_Data */ $_core = Mage::helper(strtolower('Mana_Core'));
    		$candidateNames = array($seoName, Mage::getStoreConfigFlag('mana_filters/seo/use_label') ? $_core->urlToLabel($seoName) : $_core->lowerCased($seoName));
    		/*@var $resource ManaPro_FilterSeoLinks_Resource_Rewrite */ $resource = $this->_getResource();
    		return $resource->getFilterName($candidateNames);
    	}
    }
    
    protected function _getFilterValue($name, $seoValue) {
    	/*@var $resource ManaPro_FilterSeoLinks_Resource_Rewrite */ $resource = $this->_getResource();
		$vars = Mage::helper('manapro_filterseolinks')->getUrlVars();
    	if ($name == 'cat') {
    		return $resource->getCategoryValue($this, $seoValue);
    	}
    	elseif(isset($vars[$name])) {
    		return $seoValue;
    	}
    	else {
	    	/* @var $_core Mana_Core_Helper_Data */ $_core = Mage::helper(strtolower('Mana_Core'));
	    	$candidateValues = array($seoValue, $_core->urlToLabel($seoValue));
    		return $resource->getFilterValue($this, $name, $candidateValues);
    	}
    }
    protected function _setPageCategoryId($pageId) {
        $page = Mage::getModel('cms/page');
        $page->setStoreId(Mage::app()->getStore()->getId());
        $page->load($pageId);
        $xml = simplexml_load_string(
            '<layout>'.
            ($page->getCustomLayoutUpdateXml() ? $page->getCustomLayoutUpdateXml() : $page->getLayoutUpdateXml()).
            '</layout>');
        $nodes = $xml->xpath('//category_id');
        if ($nodes && isset($nodes[0])) {
            $this->setCategoryId((string)$nodes[0]);
        }
    }
}