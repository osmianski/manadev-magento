<?php
/** 
 * @category    Mana
 * @package     Mana_Seo
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Seo_Rewrite_Url extends Mage_Core_Model_Url {
    protected $_escape = false;

    public function setEscape($value) {
        $this->_escape = $value;

        return $this;
    }

    public function getUrl($routePath = null, $routeParams = null) {
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

    public function encodeUrl($routePath, $result) {
        /* @var $seo Mana_Seo_Helper_Data */
        $seo = Mage::helper('mana_seo');

        $request = Mage::app()->getRequest();
        $routePath = explode('/', $routePath);
        if (isset($routePath[0]) && $routePath[0] == '*') $routePath[0] = $request->getRouteName();
        if (isset($routePath[1]) && $routePath[1] == '*') $routePath[1] = $request->getControllerName();
        if (isset($routePath[2]) && $routePath[2] == '*') $routePath[2] = $request->getActionName();
        $routePath = $routePath[0] . (isset($routePath[1]) ? '/' . $routePath[1] : '') . (isset($routePath[2]) ? '/' . $routePath[2] : '');

        foreach ($seo->getPageTypes() as $pageType) {
            if ($pageType->recognizeRoute($routePath)) {
                $this->_encodeUrl($pageType->getSuffix(), $result);
                break;
            }
        }

        return $result;
    }

    protected function _encodeUrl($suffix, &$result) {
        $mode = Mana_Seo_Model_Context::MODE_DIAGNOSTIC;

        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper('mana_core');

        /* @var $seo Mana_Seo_Helper_Data */
        $seo = Mage::helper('mana_seo');

        $parts = parse_url(str_replace('&amp;', '&', $result));

        $schema = $seo->getSchemaVariationPoint()->getActiveSchema();
        if (isset($parts['query']) && $schema && $schema->getQuerySeparator() && $schema->getParamSeparator() &&
            $schema->getFirstValueSeparator() && $schema->getMultipleValueSeparator())
        {
            $path = $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '') . $parts['path'];

            // check base URL
            $result = '';
            foreach (Mage::app()->getStores() as $store) {
                /* @var $store Mage_Core_Model_Store */
                $baseUrl = $store->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, strtolower($parts['scheme']) == 'https');
                if ($core->startsWith($path, $baseUrl)) {
                    $result = $baseUrl;
                    break;
                }
            }
            if ($mode == Mana_Seo_Model_Context::MODE_DIAGNOSTIC && !$result) {
                new Exception($seo->__('Unknown base URL in %s', $path));
            }
            $path = substr($path, strlen($result));

            // check suffix
            if ($mode == Mana_Seo_Model_Context::MODE_DIAGNOSTIC && !$core->endsWith($path, $suffix)) {
                new Exception($seo->__('Suffix in URL %s is not as expected (%s)', $path, $suffix));
            }
            $path = substr($path, 0, strlen($path) - strlen($suffix));

            // load all definitions (parameters, conflicting values, etc) once into memory.
            foreach ($seo->getParameterHandlers() as $parameterHandler) {
                $parameterHandler->prepareForParameterEncoding();
            }

            // sort parameters
            parse_str($parts['query'], $originalQuery);
            uksort($originalQuery, array($seo->getParameterComparer(array_keys($originalQuery)), 'compare'));

            // encode parameters into URL string
            $newQuery = '';
            $encodedQuery = '';
            foreach ($originalQuery as $parameter => $value) {
                $handled = false;
                foreach ($seo->getParameterHandlers() as $parameterHandler) {
                    if (($encoded = $parameterHandler->encodeParameter($parameter, $value)) !== false) {
                        if ($encodedQuery) {
                            $encodedQuery .= $schema->getParamSeparator();
                        }
                        $encodedQuery .= $encoded;
                        $handled = true;
                        break;
                    }
                }
                if (!$handled) {
                    if ($newQuery) {
                        $newQuery .= '&';
                    }
                    $newQuery .= $parameter .'='. $value;
                }

            }
        }
    }
}