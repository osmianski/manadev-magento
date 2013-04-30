<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterSeoLinks
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Rewrite of Mage_Core_Model_Url which makes product list pager, store view switcher and layered navigation links
 * SEO firendly.
 * @author Mana Team
 *
 */
class ManaPro_FilterSeoLinks_Model_Url extends Mage_Core_Model_Url {
    public function getUrl($routePath=null, $routeParams=null) {
        $this->_escape = isset($routeParams['_escape']) ? $routeParams['_escape'] : isset($routeParams['_m_escape']);
        $result = $this->encodeUrl($routePath, parent::getUrl($routePath, $routeParams));
        $result = preg_replace('#\/[-_\w\d]+\/\.#', '.', $result);
        if (strpos($result, '/.html') !== false) {
            $currentUrl = parent::getUrl('*/*/*', array('_current' => true, '_use_rewrite' => true, '_secure' => Mage::app()->getFrontController()->getRequest()->isSecure()));
            Mage::log("Wrong URL {$result} on page {$currentUrl}", Zend_Log::DEBUG, 'seo_errors.log');
            try {
                throw new Exception();
            } catch (Exception $e) {
                Mage::log("{$e->getMessage()}\n{$e->getTraceAsString()}", Zend_Log::DEBUG, 'seo_errors.log');
            }
        }
        return $result;
    }
    protected $_escape = false;
    public function setEscape($value) {
        $this->_escape = $value;
        return $this;
    }
    public function encodeUrl($routePath, $result) {
		$request = Mage::app()->getRequest();
		$routePath = explode('/', $routePath);
		if (isset($routePath[0]) && $routePath[0] == '*') $routePath[0] = $request->getRouteName();
		if (isset($routePath[1]) && $routePath[1] == '*') $routePath[1] = $request->getControllerName();
		if (isset($routePath[2]) && $routePath[2] == '*') $routePath[2] = $request->getActionName();
		$routePath = $routePath[0].(isset($routePath[1]) ? '/'.$routePath[1] : '').(isset($routePath[2]) ? '/'.$routePath[2] : '');
		$currentPath = $request->getRouteName().'/'.$request->getControllerName().'/'.$request->getActionName();
		if ($routePath == 'catalog/category/view' || $routePath == 'manapro_filterajax/category/view') {
		    $this->_encodeUrl($routePath, $result);
		}
		elseif ($routePath == 'cms/index/index' || $routePath == 'manapro_filterajax/index/index') {
		    $this->_encodeUrl($routePath, $result, array(
		        'handleCategorySuffix' => false,
		    ));
		}
        elseif ($routePath == 'cms/page/view' || $routePath == 'manapro_filterajax/page/view') {
            $this->_encodeUrl($routePath, $result, array(
		        'handleCategorySuffix' => false,
		    ));
        }
		return $result;
    }
    protected function _encodeUrl($routePath, &$result, $options = array()) {
        $options = array_merge(array(
            'handleCategorySuffix' => true,
        ), $options);

        $parts = parse_url(str_replace('&amp;', '&', $result));

        /* @var $core Mana_Core_Helper_Data */ $core = Mage::helper(strtolower('Mana_Core'));
        /* @var $resource ManaPro_FilterSeoLinks_Resource_Rewrite */ $resource = Mage::getResourceSingleton('manapro_filterseolinks/rewrite');
        $conditionalWord = $core->getStoreConfig('mana_filters/seo/conditional_word');
        $categorySuffix = $options['handleCategorySuffix'] ? Mage::helper('manapro_filterseolinks')->getCategoryUrlSuffix() : '';
        $showAllSuffix = $core->getStoreConfig('mana_filters/seo/show_all_suffix');
        if ($showAllSuffix) $showAllSeoSuffix = $showAllSuffix;
        $vars = Mage::helper('manapro_filterseolinks')->getUrlVars();

        $path = $parts['scheme'].'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '').$parts['path'];
        $result = '';
        foreach (Mage::app()->getStores() as /* @var $store Mage_Core_Model_Store */ $store) {
            foreach (array(false, true) as $secure) {
                $baseUrl = $store->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, $secure);
                if ($core->startsWith($path, $baseUrl)) {
                    $result = $baseUrl;
                    break;
                }
            }
        }
        if (!$result) throw new Exception('Not implemented');
        $path = substr($path, strlen($result));

        if ($categorySuffix) {
            if ($core->endsWith($path, $categorySuffix)) {
                $path = substr($path, 0, strlen($path) - strlen($categorySuffix));
                $categorySuffixSubtracted = true;
            }
            else {
                $categorySuffixSubtracted = false;
            }
        }
        $result .= $path;
        $leftQuery = '';
        if (isset($parts['query'])) {
            $query = array();
            parse_str($parts['query'], $query);
            $seoQuery = '';
            foreach ($query as $key => $value) {
                if ($showAllSuffix && strrpos($key, $showAllSuffix) === strlen($key) - strlen($showAllSuffix)) {
                    if ($value == 1) {
                        $filterCode = $core->hyphenCased(substr($key, 0, strlen($key) - strlen($showAllSuffix)));
                        if ($filterCode == 'cat') {
                            $filterCode = 'category';
                        }
                        $seoQuery .= '/'.$filterCode.$showAllSeoSuffix;
                    }
                }
                else {
                    if ($key == 'cat') {
                        if (strpos($value, '__0__') === false) {
                            $valueLabel = array();
                            foreach (explode('_', $value) as $valueFragment) {
                                $valueLabel[] = $resource->getCategoryLabel($valueFragment);
                            }
                            $seoValue = implode('_', $valueLabel);
                        }
                        else {
                            $seoValue = $value;
                        }
                        if ($seoValue !== '') {
                            $seoQuery .= '/' . Mage::helper('manapro_filterseolinks')->getCategoryName() . '/' . $seoValue;
                        }
                    }
                    elseif(isset($vars[$key])) {
                        if ($value !== '') {
                            $seoQuery .= '/' . $vars[$key] . '/' . $value;
                        }
                    }
                    elseif ($filter = $resource->isFilterName($key)) {
                        $seoName = Mage::getStoreConfigFlag('mana_filters/seo/use_label') ? $core->labelToUrl($filter->getLowerCaseName()) : $core->hyphenCased($key);
                        if (strpos($value, '__0__') === false) {
                            $valueLabel = array();
                            foreach (explode('_', $value) as $valueFragment) {
                                $valueLabel[] = $resource->getFilterValueLabel($key, $valueFragment);
                            }
                            $seoValue = implode('_', $valueLabel);
                        }
                        else {
                            $seoValue = $value;
                        }
                        if ($seoValue !== '') {
                            $seoQuery .= '/' . $seoName . '/' . $seoValue;
                        }
                    }
                    elseif ($key == 'm-layered') {
                        if ($leftQuery) $leftQuery .= '&';;
                        $leftQuery .= $key . '=1';
                    }
                    else {
                        if ($leftQuery) $leftQuery .= '&';//'&amp;';
                        $leftQuery .= $key . '=' . $value;
                    }
                }
            }
            if ($seoQuery) {
                if (!$core->endsWith($result, '/')) {
                    $result .= '/';
                }
                $result .= $conditionalWord.$seoQuery;
            }
        }
        if ($categorySuffix) $result .= $categorySuffix;
        if ($leftQuery) $result .= '?' . $leftQuery;
        if ($this->_escape) {
            $result = $this->escape($result);
        }

    }

    public function encodeValue($code, $value) {
        $resource = Mage::getResourceSingleton('manapro_filterseolinks/rewrite');
        /* @var $resource ManaPro_FilterSeoLinks_Resource_Rewrite */
        if ($code == 'cat') {
            return $resource->getCategoryLabel($value);
        }
        else {
            return $resource->getFilterValueLabel($code, $value);
        }
    }
}