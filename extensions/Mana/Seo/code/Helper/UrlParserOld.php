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
class Mana_Seo_Helper_UrlParserOld extends Mage_Core_Helper_Abstract {
    const STATUS_OK = 1;
    const STATUS_OBSOLETE = 2;
    const STATUS_CORRECTION = 3;

    /**
     * @param $path
     * @return Mana_Seo_Model_ParsedUrl | bool
     */
    public function parseUrlPath($path) {
        $storeId = Mage::app()->getStore()->getId();
        $status = self::STATUS_OK;
        $isDiagnosing = Mage::getStoreConfigFlag('mana/seo/is_diagnosing');

        $originalSlash = (substr($path, -1) == '/') ? '/' : '';
        $alternativeSlash = $originalSlash ? '' : '/';
        $path = trim($path, '/');

        $this->_getSchemas($storeId, $activeSchemas, $obsoleteSchemas);

        $handlers = $this->_addHandler(array(), '_registerUrl');
        $results = array();

        foreach ($activeSchemas as $schema) {
            if (($result = $this->_registerResult($results, $isDiagnosing,
                $this->_parsePage($path, $storeId, $schema, $status, $isDiagnosing,
                    $originalSlash, $alternativeSlash, $handlers))) !== false)
            {
                return $result[0];
            }
        }

        $status = self::STATUS_OBSOLETE;
        foreach ($obsoleteSchemas as $schema) {
            if (($result = $this->_registerResult($results, $isDiagnosing,
                $this->_parsePage($path, $storeId, $schema, $status, $isDiagnosing,
                    $originalSlash, $alternativeSlash, $handlers))) !== false)
            {
                return $result[0];
            }
        }
        return $this->_registerDiagnosis($results);
    }

    /**
     * @param string $path
     * @param int $storeId
     * @param Mana_Seo_Model_Schema $schema
     * @param int $parentStatus
     * @param bool $isDiagnosing
     * @param string $originalSlash
     * @param string $alternativeSlash
     * @param array $handlers
     * @return Mana_Seo_Model_ParsedUrl[]
     */
    protected function _parsePage($path, $storeId, $schema, $parentStatus, $isDiagnosing, $originalSlash,
        $alternativeSlash, $handlers)
    {
        if (!$path) {
            if (($result = $this->_createParsedUrl($parentStatus, $isDiagnosing,
                $this->_addHandler($handlers, '_registerHomePage'))) !== false)
            {
                return $result;
            }
        }

        $separators = $this->_getSchemaSeparators($schema);

        /* @var $urlKeyProvider Mana_Seo_Helper_UrlKeyProvider_Database */
        $urlKeyProvider = Mage::helper('mana_seo/urlKeyProvider_database');

        $candidates = $this->_getCandidates($path, $separators, true);
        $this->_getPageFlags($schema, $isPage, $isParameter, $isFirstValue, $isMultipleValue);

        $urlKeyProvider->getUrlKeys($candidates, $storeId, $isPage, $isParameter, $isFirstValue,
            $isMultipleValue, $activeUrlKeys, $obsoleteUrlKeys);

        $status = $parentStatus;
        $results = array();

        foreach ($activeUrlKeys as $urlKey) {
            if (($result = $this->_registerResult($results, $isDiagnosing,
                $this->_parseSuffix($path, $storeId, $schema, $urlKey, $status, $isDiagnosing,
                    $originalSlash, $alternativeSlash, $separators,
                    $this->_addHandler($handlers, '_registerPage', $urlKey)))) !== false)
            {
                return $result;
            }
        }

        $status = self::STATUS_OBSOLETE;
        foreach ($obsoleteUrlKeys as $urlKey) {
            if (($result = $this->_registerResult($results, $isDiagnosing,
                $this->_parseSuffix($path, $storeId, $schema, $urlKey, $status, $isDiagnosing,
                    $originalSlash, $alternativeSlash, $separators,
                    $this->_addHandler($handlers, '_registerPage', $urlKey)))) !== false)
            {
                return $result;
            }
        }

        return $results;
    }

    /**
     * @param string $parentPath
     * @param int $storeId
     * @param Mana_Seo_Model_Schema $schema
     * @param Mana_Seo_Model_Url $parentUrl
     * @param string $parentStatus
     * @param bool $isDiagnosing
     * @param string $originalSlash
     * @param $alternativeSlash
     * @param string[] $separators
     * @param array $handlers
     * @return Mana_Seo_Model_ParsedUrl[]
     */
    protected function _parseSuffix($parentPath, $storeId, $schema, $parentUrl, $parentStatus, $isDiagnosing,
        $originalSlash, $alternativeSlash, $separators, $handlers)
    {
        if ($parentPath == $parentUrl->getUrlKey()) {
            return $this->_createParsedUrl($parentStatus, $handlers);
        }
        else {
            /* @var $mbstring Mana_Core_Helper_Mbstring */
            $mbstring = Mage::helper('mana_core/mbstring');

            $path = $mbstring->substr($parentPath, $mbstring->strlen($parentUrl->getUrlKey()));
            $separator = '';
            foreach ($separators as $candidateSeparator) {
                if ($mbstring->startsWith($path, $candidateSeparator)) {
                    $separator = $candidateSeparator;
                    $path = $mbstring->substr($path, $mbstring->strlen($separator));
                    break;
                }
            }
        }

        $parentUrl->getHelper()->getSuffixes($path, $originalSlash, $storeId, $activeSuffixes, $obsoleteSuffixes);

        $status = $parentStatus;
        $results = array();

        foreach ($activeSuffixes as $suffix) {
            if (($result = $this->_registerResult($results, $isDiagnosing,
                $this->_parseParameter($path, $storeId, $schema,
                    $parentUrl, $status, $isDiagnosing, $originalSlash, $alternativeSlash, $separators,
                    $suffix, $separator,
                    $this->_addHandler($handlers, '_registerSuffix', $suffix)))) !== false)
            {
                return $result;
            }
        }

        $status = self::STATUS_OBSOLETE;
        foreach ($obsoleteSuffixes as $suffix) {
            if (($result = $this->_registerResult($results, $isDiagnosing,
                $this->_parseParameter($path, $storeId, $schema,
                    $parentUrl, $status, $isDiagnosing, $originalSlash, $alternativeSlash, $separators,
                    $suffix, $separator,
                    $this->_addHandler($handlers, '_registerSuffix', $suffix)))) !== false)
            {
                return $result;
            }
        }

        return $results;
    }


    /**
     * @param string $parentPath
     * @param int $storeId
     * @param Mana_Seo_Model_Schema $schema
     * @param Mana_Seo_Model_Url | string $parentUrl
     * @param string $parentStatus
     * @param bool $isDiagnosing
     * @param $originalSlash
     * @param $alternativeSlash
     * @param string[] $separators
     * @param string | bool $parentSuffix
     * @param string | bool $separator
     * @param array $handlers
     * @return Mana_Seo_Model_ParsedUrl[] | Mana_Seo_Model_ParsedUrl | bool
     */
    protected function _parseParameter($parentPath, $storeId, $schema, $parentUrl, $parentStatus, $isDiagnosing,
        $originalSlash, $alternativeSlash, $separators, $parentSuffix, $separator, $handlers)
    {
        /* @var $mbstring Mana_Core_Helper_Mbstring */
        $mbstring = Mage::helper('mana_core/mbstring');

        if ($parentSuffix !== false) {
            if ($parentPath == $parentSuffix) {
                return $this->_createParsedUrl($parentStatus, $handlers);
            }
            else {
                $path = $this->_removeSuffix($parentPath, $parentSuffix);
            }
        }
        else {
            $urlKey = is_string($parentUrl) ? $parentUrl : $parentUrl->getUrlKey();
            if ($parentPath == $urlKey) {
                return $this->_createParsedUrl($parentStatus, $handlers);
            }
            else {
                $path = $mbstring->substr($parentPath, $mbstring->strlen($urlKey));
                foreach ($separators as $candidateSeparator) {
                    if ($mbstring->startsWith($path, $candidateSeparator)) {
                        $separator = $candidateSeparator;
                        $path = $mbstring->substr($path, $mbstring->strlen($separator));
                        break;
                    }
                }
            }
        }

        /* @var $urlKeyProvider Mana_Seo_Helper_UrlKeyProvider */
        $urlKeyProvider = Mage::helper(is_string($parentUrl)
            ? 'mana_seo/urlKeyProvider_database'
            : $parentUrl->getUrlKeyProvider());

        $candidates = $this->_getCandidates($path, $separators);
        $this->_getSeparatorFlags($schema, $separator, $isPage, $isParameter, $isFirstValue, $isMultipleValue);
        $urlKeyProvider->getUrlKeys($candidates, $storeId, $isPage, $isParameter, $isFirstValue,
            $isMultipleValue, $activeUrlKeys, $obsoleteUrlKeys);

        $status = $parentStatus;
        $results = array();

        foreach ($activeUrlKeys as $urlKey) {
            if (($result = $this->_registerResult($results, $isDiagnosing,
                $this->_parseParameter($path, $storeId, $schema, $urlKey, $status, $isDiagnosing,
                    $originalSlash, $alternativeSlash, $separators, false, false,
                    $this->_addHandler($handlers, '_registerParameter', $urlKey)))) !== false)
            {
                return $result;
            }
        }

        $status = self::STATUS_OBSOLETE;
        foreach ($obsoleteUrlKeys as $urlKey) {
            if (($result = $this->_registerResult($results, $isDiagnosing,
                $this->_parseParameter($path, $storeId, $schema, $urlKey, $status, $isDiagnosing,
                    $originalSlash, $alternativeSlash, $separators, false, false,
                    $this->_addHandler($handlers, '_registerParameter', $urlKey)))) !== false)
            {
                return $result;
            }
        }

        $status = self::STATUS_CORRECTION;
        if (!count($results)) {
            if (($result = $this->_registerResult($results, $isDiagnosing,
                $this->_parseParameter($path, $storeId, $schema, $candidates[0], $status, $isDiagnosing,
                    $originalSlash, $alternativeSlash, $separators, false, false, $handlers))) !== false)
            {
                return $result;
            }
        }
        return $results;
    }

    /**
     * @param bool $status
     * @param array $handlers
     * @return Mana_Seo_Model_ParsedUrl[] | bool
     */
    protected function _createParsedUrl($status, $handlers) {
        /* @var $parsedUrl Mana_Seo_Model_ParsedUrl */
        $parsedUrl = Mage::getModel('mana_seo/parsedUrl');
        $parsedUrl->setStatus($status);
        foreach (array_reverse($handlers) as $handler) {
            $args = $handler[1];
            array_unshift($args, $parsedUrl);
            if (!call_user_func_array($handler[0], $args)) {
                return false;
            }
        }
        return array($parsedUrl);
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl $parsedUrl
     * @return bool
     */
    protected function _registerHomePage($parsedUrl) {
        /* @var $helper mana_Seo_Helper_Url_HomePage */
        $helper = Mage::helper('mana_seo/url_homePage');
        return $helper->registerPage($parsedUrl, null);
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl $parsedUrl
     * @return bool
     */
    protected function _registerUrl($parsedUrl) {
        $parsedUrl->setPath($this->_addSuffix($parsedUrl->getPageUrlKey(), $parsedUrl->getSuffix()));
        return true;
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl $parsedUrl
     * @param Mana_Seo_Model_Url $urlKey
     * @throws Exception
     * @return bool
     */
    protected function _registerPage($parsedUrl, $urlKey) {
        if ($urlKey->getIsPage()) {
            return $urlKey->getHelper()->registerPage($parsedUrl, $urlKey);
        }
        else {
            /* @var $helper mana_Seo_Helper_Url_HomePage */
            $helper = Mage::helper('mana_seo/url_homePage');
            if (!$helper->registerPage($parsedUrl, null)) {
                return false;
            }
            return $this->_registerParameter($parsedUrl, $urlKey);
        }
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl $parsedUrl
     * @param string $suffix
     * @return bool
     */
    protected function _registerSuffix($parsedUrl, $suffix) {
        $parsedUrl->setSuffix($suffix);
        return true;
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl $parsedUrl
     * @param Mana_Seo_Model_Url $urlKey
     * @throws Exception
     * @return Mana_Seo_Model_ParsedUrl[] | Mana_Seo_Model_ParsedUrl
     */
    protected function _registerParameter($parsedUrl, $urlKey) {
        if ($urlKey->getIsParameter()) {
            return $urlKey->getHelper()->registerParameter($parsedUrl, $urlKey);
        }
        elseif ($urlKey->getIsValue()) {
            return $urlKey->getHelper()->registerValue($parsedUrl, $urlKey);
        }
        else {
            throw new Exception('Not implemented');
        }
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl[] $results
     * @param bool $isDiagnosing
     * @param Mana_Seo_Model_ParsedUrl[] | bool $parsedUrls
     * @return Mana_Seo_Model_ParsedUrl[] | bool
     */
    protected function _registerResult(&$results, $isDiagnosing, $parsedUrls) {
        if ($parsedUrls === false) {
            return false;
        }
        elseif ($isDiagnosing || !count($parsedUrls) || $parsedUrls[0]->getStatus() !== self::STATUS_OK) {
            $results = array_merge($results, $parsedUrls);
            return false;
        }
        else {
            return $parsedUrls;
        }
    }


    /**
     * @param Mana_Seo_Model_ParsedUrl[] $parsedUrls
     * @return Mana_Seo_Model_ParsedUrl | bool
     */
    protected function _registerDiagnosis($parsedUrls) {
        /* @var $result Mana_Seo_Model_ParsedUrl */
        $result = false;
        foreach ($parsedUrls as $parsedUrl) {
            if (!$result || $result->getStatus() > $parsedUrl->getStatus()) {
                $result = $parsedUrl;
                if ($result->getStatus() == self::STATUS_OK) {
                    break;
                }
            }
        }
        return $result;
    }

    /**
     * @param int $storeId
     * @param Mana_Seo_Model_Schema[] $activeSchemas
     * @param Mana_Seo_Model_Schema[] $obsoleteSchemas
     * @param bool $onlyActive
     */
    protected function _getSchemas($storeId, &$activeSchemas, &$obsoleteSchemas, $onlyActive = false) {
        $activeSchemas = array();
        $obsoleteSchemas = array();

        /* @var $dbHelper Mana_Db_Helper_Data */
        $dbHelper = Mage::helper('mana_db');

        /* @var $collection Mana_Db_Resource_Entity_Collection */
        $collection = $dbHelper->getResourceModel('mana_seo/schema/store_flat_collection');
        $collection->setStoreFilter($storeId);
        if ($onlyActive) {
            $collection->addFieldToFilter('status', Mana_Seo_Model_Schema::STATUS_ACTIVE);
        }
        else {
            $collection->addFieldToFilter('status', array(
                'in' => array(
                    Mana_Seo_Model_Schema::STATUS_ACTIVE,
                    Mana_Seo_Model_Schema::STATUS_OBSOLETE
                )
            ));
        }

        foreach ($collection as $schema) {
            /* @var $schema Mana_Seo_Model_Schema */
            if ($schema->getStatus() == Mana_Seo_Model_Schema::STATUS_ACTIVE) {
                $activeSchemas[] = $schema;
            }
            else {
                $obsoleteSchemas[] = $schema;
            }
        }
    }

    /**
     * @param Mana_Seo_Model_Schema $schema
     * @return string[]
     */
    protected function _getSchemaSeparators($schema) {
        /* @var $mbstring Mana_Core_Helper_Mbstring */
        $mbstring = Mage::helper('mana_core/mbstring');

        $result = array();
        $result[$schema->getQuerySeparator()] = $mbstring->strlen($schema->getQuerySeparator());
        $result[$schema->getParamSeparator()] = $mbstring->strlen($schema->getParamSeparator());
        $result[$schema->getFirstValueSeparator()] = $mbstring->strlen($schema->getFirstValueSeparator());
        $result[$schema->getMultipleValueSeparator()] = $mbstring->strlen($schema->getMultipleValueSeparator());

        arsort($result);
        return array_keys($result);
    }

    /**
     * @param string $path
     * @param string[] $separators
     * @param bool $addSuffixedCandidate
     * @return string[]
     */
    protected function _getCandidates($path, $separators, $addSuffixedCandidate = false) {
        /* @var $mbstring Mana_Core_Helper_Mbstring */
        $mbstring = Mage::helper('mana_core/mbstring');

        $pos = 0;
        $nextPos = true;
        $candidates = array();
        while ($nextPos !== false) {
            $nextPos = false;
            foreach ($separators as $separator) {
                if (($sepPos = $mbstring->strpos($path, $separator, $pos)) !== false && ($nextPos === false || $sepPos < $nextPos)) {
                    $nextPos = $sepPos;
                }
            }
            if ($nextPos !== false) {
                $candidates[] = $mbstring->substr($path, 0, $nextPos);
                $pos = $nextPos + 1;
            }
        }
        $candidates[] = $path;
        if ($addSuffixedCandidate && (($pos = $mbstring->strpos($path, '.')) !== false)) {
            $candidates[] = $mbstring->substr($path, 0, $pos);
        }

        return $candidates;
    }

    /**
     * @param Mana_Seo_Model_Schema $schema
     * @param bool $isPage
     * @param bool $isParameter
     * @param bool $isFirstValue
     * @param bool $isMultipleValue
     */
    protected function _getPageFlags($schema, &$isPage, &$isParameter, &$isFirstValue, &$isMultipleValue) {
        $isPage = true;
        $isParameter = true; // in case of home page URL path starts with a parameter
        $isFirstValue = false;
        $isMultipleValue = false;
    }

    /**
     * @param Mana_Seo_Model_Schema $schema
     * @param string $separator
     * @param bool $isPage
     * @param bool $isParameter
     * @param bool $isFirstValue
     * @param bool $isMultipleValue
     */
    protected function _getSeparatorFlags($schema, $separator, &$isPage, &$isParameter, &$isFirstValue, &$isMultipleValue) {
        $isPage = false;
        $isParameter = ($separator == $schema->getParamSeparator());
        $isFirstValue = ($separator == $schema->getFirstValueSeparator());
        $isMultipleValue = ($separator == $schema->getMultipleValueSeparator());
    }

    protected function _removeSuffix($path, $suffix) {
        /* @var $mbstring Mana_Core_Helper_Mbstring */
        $mbstring = Mage::helper('mana_core/mbstring');

        if ($suffix && $suffix != '/') {
            return $mbstring->substr($path, 0,
                $mbstring->strlen($path) - $mbstring->strlen($suffix));
        }
        else {
            return $path;
        }
    }

    protected function _addSuffix($path, $suffix) {
        if ($suffix && $suffix != '/') {
            return $path . $suffix;
        }
        else {
            return $path;
        }
    }

    /**
     * @param array $handlers
     * @param string $method
     * @return array
     */
    protected function _addHandler($handlers, $method) {
        $handlers[] = array(array($this, $method), array_slice(func_get_args(), 2));
        return $handlers;

    }
}