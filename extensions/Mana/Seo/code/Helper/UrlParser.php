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
class Mana_Seo_Helper_UrlParser extends Mage_Core_Helper_Abstract  {
    const IS_PAGE = 1;
    const IS_PARAMETER = 2;
    const IS_ATTRIBUTE_VALUE = 4;
    const IS_CATEGORY_VALUE = 8;

    const CONFLICT_ATTRIBUTE_VALUE = 1;
    const CONFLICT_PAGE = 2;
    const CONFLICT_PARAMETER = 3;
    const CONFLICT_CATEGORY_VALUE = 4;

    /**
     * @var int
     */
    protected $_storeId;

    /**
     * @var Mana_Seo_Model_Schema
     */
    protected $_schema;

    protected $_results;

    protected $_conflicts;

    protected $_diagnosticMode = false;

    protected $_path;
    /**
     * @var Mana_Seo_Model_ParsedUrl[]
     */
    protected $_parsedUrls;

    protected $_allSuffixes;

    protected $_suffixesByPageType = array();

    /**
     * @var Mana_Seo_Resource_Url_Collection
     */
    protected $_parameterUrls;
    #region Facade

    /**
     * @param string $path
     * @return Mana_Seo_Model_ParsedUrl | bool
     */
    public function parse($path) {
        $this->_storeId = Mage::app()->getStore()->getId();
        $this->_path = $path;
        $this->_results = array();
        $this->_conflicts = array();
        $this->_diagnosticMode = Mage::getStoreConfig('mana/seo/diagnostic_mode');

        $this->_getSchemas($activeSchemas, $obsoleteSchemas);

        /* @var $token Mana_Seo_Model_ParsedUrl */
        $token = Mage::getModel('mana_seo/parsedUrl');

        foreach ($activeSchemas as $schema) {
            $this->_schema = $schema;
            $token
                ->setTextToBeParsed($path)
                ->setStatus(Mana_Seo_Model_ParsedUrl::STATUS_OK);

            if ($this->_parseUrlPath(clone $token)) {
                return $this->_processResult();
            }
        }
        foreach ($obsoleteSchemas as $schema) {
            $this->_schema = $schema;
            $token->setStatus(Mana_Seo_Model_ParsedUrl::STATUS_OBSOLETE);

            if ($this->_parseUrlPath(clone $token)) {
                return $this->_processResult();
            }
        }

        return $this->_processResult();
    }
    #endregion

    #region Parser
    /**
     * Parses $token->getTextToBeParsed() according to the following syntax rule:
     *      UrlPath ::= ["/"][PageUrlKey][QuerySeparator Parameters][Suffix]["/"] .
     *
     * Uses $this->_schema URL settings (m_seo_schema_XXX tables), puts all found matches into $this->_parsedUrls.
     *
     * @param Mana_Seo_Model_ParsedUrl $token
     * @return bool
     */
    protected function _parseUrlPath($token) {
        $pageToken = clone $token
            ->setSlash((substr($token->getTextToBeParsed(), -1) == '/') ? '/' : '')
            ->setTextToBeParsed(rtrim($token->getTextToBeParsed(), '/'));

        // split by "/", split suffix
        $suffixes = $this->_scanSuffixes($pageToken, $this->_getAllSuffixes($pageToken));
        $tokens = $this->_scanUntilSeparatorOrSuffix($suffixes, $this->_schema->getQuerySeparator());

        // get all valid page URL key and suffix combinations (including home page) based on given URL path
        if ($tokens = $this->_getPageUrlKeysAndRemoveSuffixes($tokens)) {
            foreach ($tokens as $token) {
                $this->_setPage($token);

                if ($this->_parseParameters($token)) {
                    return true;
                }
            }
        }

        // home page
        if ($tokens = $this->_scanSuffixes($pageToken, $this->_getSuffixesByType($token, 'home_page'))) {
            foreach ($tokens as $token) {
                $this->_setHomePage($token);

                if ($this->_parseParameters($token)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Parameters ::= Parameter {ParameterSeparator Parameter } .
     * @param Mana_Seo_Model_ParsedUrl $token
     * @return bool
     */
    protected function _parseParameters($token) {
        if (!$token->getTextToBeParsed()) {
            return $this->_setResult($token);
        }

        // split by "/", add correction for token beginning with "/"
        if ($tokens = $this->_scanUntilSeparator($token, $this->_schema->getParamSeparator())) {
            foreach ($tokens as $token) {
                // process token parameter and then continue parsing all parameters in nextToken.
                // Return if exact match found
                if ($this->_parseParameter($token)) {
                    return true;
                }
            }
        }

        return false;
    }


    /**
     * Parameter ::= [Key FirstValueSeparator] Values .
     * @param Mana_Seo_Model_ParsedUrl $token
     * @throws Exception
     * @return bool
     */
    protected function _parseParameter($token) {
        $subToken = $token->zoomIn();

        // split by "/", add correction for token beginning with "/"
        $tokens = $this->_scanUntilSeparator($subToken, $this->_schema->getFirstValueSeparator());

        if ($tokens) {
            foreach ($tokens as $token) {
                // eat "/", mark as correction if there are no values after "/"
                if ($token->getTextToBeParsed()) {
                    if ($this->_getParameterUrlKey($token)) {
                        $token
                            ->setAttributeId($token->getParameterUrl()->getAttributeId())
                            ->setAttributeCode($token->getParameterUrl()->getAttributeCode());
                        switch ($token->getParameterUrl()->getType()) {
                            case Mana_Seo_Model_ParsedUrl::PARAMETER_ATTRIBUTE:
                                if ($this->_parseAttributeValues($token)) {
                                    return true;
                                }
                                break;
                            case Mana_Seo_Model_ParsedUrl::PARAMETER_CATEGORY:
                                if ($this->_parseCategoryValues($token)) {
                                    return true;
                                }
                                break;
                            case Mana_Seo_Model_ParsedUrl::PARAMETER_PRICE:
                                if ($this->_parsePriceValues($token)) {
                                    return true;
                                }
                                break;
                            case Mana_Seo_Model_ParsedUrl::PARAMETER_TOOLBAR:
                                if ($this->_parseToolbarValues($token)) {
                                    return true;
                                }
                                break;
                            default:
                                throw new Exception('Not implemented');
                        }
                        return false;
                    }
                }
                $subToken->setAttributeId(false)->setAttributeCode(false);
                return $this->_parseAttributeValues($subToken);
            }
        }

        return false;
    }

    /**
     * AttributeValues ::= Value {MultipleValueSeparator Value } .
     * @param Mana_Seo_Model_ParsedUrl $token
     * @return bool
     */
    protected function _parseAttributeValues($token) {
        $cNotFound = Mana_Seo_Model_ParsedUrl::CORRECT_NOT_FOUND_ATTRIBUTE_FILTER_URL_KEY;

        // split by "-", add correction for token beginning with "-"
        if (($text = $token->getTextToBeParsed()) && ($tokens = $this->_scanUntilSeparator($token, $this->_schema->getMultipleValueSeparator()))) {
            // get all valid attribute value URL keys
            if ($tokens = $this->_getAttributeValueUrlKeys($tokens)) {
                foreach ($tokens as $token) {
                    $this->_setAttributeFilter($token);

                    // read the rest values. Return if exact match found
                    if ($this->_parseAttributeValues($token)) {
                        return true;
                    }
                }

                return false;
            }
            else {
                if (!$this->_correct($token, $cNotFound, __LINE__, $text)) {
                    return false;
                }
            }
        }

        return $this->_parseParameters($token->zoomOut());
    }

    /**
     * CategoryValues ::= Value {"/" Value } .
     * @param Mana_Seo_Model_ParsedUrl $token
     * @return bool
     */
    protected function _parseCategoryValues($token) {
        $cNotFound = Mana_Seo_Model_ParsedUrl::CORRECT_NOT_FOUND_CATEGORY_FILTER_URL_KEY;

        // get category id defined by page URL key, or store root category is for non category pages
        $categoryId = $this->_getCategoryIdByPageUrlKey($token);

        // scan subcategory URL key
        if ($urlKey = $this->_scanSingleUntilSeparator($token, '/')) {
            $token = $urlKey;
            if ($subCategoryId = $this->_getCategoryIdByFilterUrlKey($token, $categoryId)) {
                $categoryId = $subCategoryId;
                while ($token->getTextToBeParsed()) {
                    if ($urlKey = $this->_scanSingleUntilSeparator($token, '/')) {
                        $token = $urlKey;
                        if ($subCategoryId = $this->_getCategoryIdByFilterUrlKey($token, $categoryId)) {
                            $categoryId = $subCategoryId;
                        }
                        else {
                            if (!$this->_correct($token, $cNotFound, __LINE__, $token->getText())) {
                                return false;
                            }
                            break;
                        }
                    }
                    else {
                        break;
                    }
                }
                $this->_setCategoryFilter($token, $categoryId);
            }
            else {
                if (!$this->_correct($token, $cNotFound, __LINE__, $token->getText())) {
                    return false;
                }
            }
        }

        return $this->_parseParameters($token->zoomOut());
    }

    /**
     * PriceValues ::= Value {PriceValueSeparator Value } .
     * @param Mana_Seo_Model_ParsedUrl $token
     * @return bool
     */
    protected function _parsePriceValues($token) {
        $cInvalid = Mana_Seo_Model_ParsedUrl::CORRECT_INVALID_PRICE_FILTER_VALUE;

        $originalToken = $token;
        if ($pairs = $this->_scanNumbers($token, $this->_schema->getMultipleValueSeparator(), $this->_schema->getPriceSeparator(), '-')) {
            foreach ($pairs as $pair) {
                if (!$this->_setPriceFilter($token, $pair['from'], $pair['to'])) {
                    if (!$this->_correct($token, $cInvalid, __LINE__, json_encode($pair))) {
                        return false;
                    }
                }
            }
        }

        return $this->_parseParameters($originalToken->zoomOut());
    }

    /**
     * ToolbarValues ::= Value .
     * @param Mana_Seo_Model_ParsedUrl $token
     * @return bool
     */
    protected function _parseToolbarValues($token) {
        $cInvalid = Mana_Seo_Model_ParsedUrl::CORRECT_INVALID_TOOLBAR_VALUE;
        if (!$this->_setToolbarValue($token)) {
            if (!$this->_correct($token, $cInvalid, __LINE__, $token->getText())) {
                return false;
            }
        }

        return $this->_parseParameters($token->zoomOut());
    }

    #endregion

    #region Scanner

    /**
     * @param Mana_Seo_Model_ParsedUrl{} $token
     * @param string $separator
     * @return Mana_Seo_Model_ParsedUrl[] | bool
     */
    protected function _scanUntilSeparatorOrSuffix($tokens, $separator) {
        if (!$tokens) {
            return false;
        }

        $result = array();
        foreach ($tokens as $suffix => $suffixToken) {
            $result[$suffix] = $this->_internalScan($suffixToken, $separator, false);
        }
        return $result;
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl $token
     * @param string $separator
     * @return Mana_Seo_Model_ParsedUrl[] | bool
     */
    protected function _scanUntilSeparator($token, $separator) {
        return $this->_internalScan($token, $separator, false);
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl $token
     * @param string $separator
     * @return Mana_Seo_Model_ParsedUrl | bool
     */
    protected function _scanSingleUntilSeparator($token, $separator) {
        return $this->_internalScan($token, $separator, true);
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl $token
     * @param string[] | bool $suffixes
     * @return Mana_Seo_Model_ParsedUrl[] | bool
     */
    protected function _scanSuffixes($token, $suffixes) {
        /* @var $mbstring Mana_Core_Helper_Mbstring */
        $mbstring = Mage::helper('mana_core/mbstring');

        $text = $token->getTextToBeParsed();
        $slash = $token->getSlash();

        $tokens = array();
        foreach ($suffixes as $suffix => $active) {
            if (!$this->_internalMatchSuffix($text, $slash, $suffix)) {
                continue;
            }

            $unSuffixedToken = clone $token;
            $unSuffixedToken
                ->setSuffix($suffix)
                ->setTextToBeParsed($mbstring->substr($text, 0, $mbstring->strlen($text) - $mbstring->strlen($suffix)));

            $this->_activate($unSuffixedToken, $active);
            $tokens[$suffix] = $unSuffixedToken;
        }

        return count($tokens) ? $tokens : false;
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl $token
     * @param string $separator
     * @param bool $single
     * @return Mana_Seo_Model_ParsedUrl | Mana_Seo_Model_ParsedUrl[] | bool
     */
    protected function _internalScan($token, $separator, $single) {
        /* @var $mbstring Mana_Core_Helper_Mbstring */
        $mbstring = Mage::helper('mana_core/mbstring');

        $text = $token->getTextToBeParsed();

        $pos = 0;
        $tokens = $single ? false : array();
        $separatorLength = $mbstring->strlen($separator);
        while ($pos !== false) {
            if (($nextPos = $mbstring->strpos($text, $separator, $pos)) !== false) {
                if ($nextPos === 0) {
                    $pos = $separatorLength;
                }
                else {
                    $clonedToken = clone $token;

                    $clonedToken
                        ->setText($mbstring->substr($text, 0, $nextPos))
                        ->setTextToBeParsed($mbstring->substr($text, $nextPos + $separatorLength));

                    if ($single) {
                        return $clonedToken;
                    }
                    $tokens[$clonedToken->getText()] = $clonedToken;
                    $pos = $nextPos + $separatorLength;
                }
            }
            else {
                $clonedToken = clone $token;

                $clonedToken
                    ->setText($text)
                    ->setTextToBeParsed('');

                if ($single) {
                    return $clonedToken;
                }
                $tokens[$clonedToken->getText()] = $clonedToken;

                $pos = $nextPos;
            }
        }

        return count($tokens) ? $tokens : false;
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl $token
     * @param string $multipleValueSeparator
     * @param string $priceSeparator
     * @param string $minusSymbol
     * @return int[][] | bool
     */
    protected function _scanNumbers($token, $multipleValueSeparator, $priceSeparator, $minusSymbol) {
        $cInvalid = Mana_Seo_Model_ParsedUrl::CORRECT_INVALID_PRICE_FILTER_VALUE;

        /* @var $mbstring Mana_Core_Helper_Mbstring */
        $mbstring = Mage::helper('mana_core/mbstring');

        $text = $token->getTextToBeParsed();

        $pos = 0;
        $result = array();

        $multipleValueSeparatorLength = $mbstring->strlen($multipleValueSeparator);
        $priceSeparatorLength = $mbstring->strlen($priceSeparator);
        $minusLength = $mbstring->strlen($minusSymbol);

        while ($pos !== false) {
            $pair = array();

            // reading from minus sign
            if ($nextPos = $mbstring->strpos($text, $minusSymbol, $pos) === 0) {
                $minusText = $minusSymbol;
                $pos += $minusLength;
            }
            else {
                $minusText = '';
            }

            // reading from value
            if ($nextPos = $mbstring->strpos($text, $priceSeparator, $pos) !== false) {
                $pair['from'] = $minusText.$mbstring->substr($text, $pos, $nextPos - $pos);
                $pos = $nextPos + $priceSeparatorLength;
                if ($pair['from'] === '' || !is_numeric($pair['from'])) {
                    $this->_correct($token, $cInvalid, __LINE__, $text);
                    return false;
                }
            }
            else {
                $this->_correct($token, $cInvalid, __LINE__, $text);
                return false;
            }

            // reading to minus sign
            if ($nextPos = $mbstring->strpos($text, $minusSymbol, $pos) === 0) {
                $minusText = $minusSymbol;
                $pos += $minusLength;
            }
            else {
                $minusText = '';
            }

            // reading from value
            if ($nextPos = $mbstring->strpos($text, $multipleValueSeparator, $pos) !== false) {
                $pair['to'] = $minusText . $mbstring->substr($text, $pos, $nextPos - $pos);
                $pos = $nextPos + $multipleValueSeparatorLength;
            }
            else {
                $pair['to'] = $minusText . $mbstring->substr($text, $pos);
                $pos = false;
            }
            if ($pair['to'] === '' || !is_numeric($pair['to'])) {
                $this->_correct($token, $cInvalid, __LINE__, $text);

                return false;
            }

            $result[] = $pair;
        }

        return count($result) ? $result : false;
    }

    #endregion

    #region Data Operations
    /**
     * @param Mana_Seo_Model_Schema[] $activeSchemas
     * @param Mana_Seo_Model_Schema[] $obsoleteSchemas
     */
    protected function _getSchemas(&$activeSchemas, &$obsoleteSchemas) {
        $activeSchemas = array();
        $obsoleteSchemas = array();

        /* @var $dbHelper Mana_Db_Helper_Data */
        $dbHelper = Mage::helper('mana_db');

        /* @var $collection Mana_Db_Resource_Entity_Collection */
        $collection = $dbHelper->getResourceModel('mana_seo/schema/store_flat_collection');
        $collection
            ->setStoreFilter($this->_storeId)
            ->addFieldToFilter('status', array(
                'in' => array(
                    Mana_Seo_Model_Schema::STATUS_ACTIVE,
                    Mana_Seo_Model_Schema::STATUS_OBSOLETE
                )
            ));

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
     * @param Mana_Seo_Model_ParsedUrl $token
     * @return string[]
     */
    protected function _getAllSuffixes($token) {
        if (!$this->_allSuffixes) {
            /* @var $seo Mana_Seo_Helper_Data */
            $seo = Mage::helper('mana_seo');

            $current = array();
            $historyType = array();
            foreach ($seo->getPageTypes() as $pageType) {
                $suffix = (string)$pageType->getCurrentSuffix();
                $type = $pageType->getSuffixHistoryType();
                $current[$suffix] = $this->_addDotToSuffix($suffix);
                $historyType[$type] = $type;
            }

            $this->_allSuffixes = $this->_getSuffixes($token, $current, $historyType);
        }
        return $this->_allSuffixes;
    }

    protected function _getSuffixesByType($token, $pageType) {
        if (!isset($this->_suffixesByPageType[$pageType])) {
            /* @var $seo Mana_Seo_Helper_Data */
            $seo = Mage::helper('mana_seo');

            $pageTypeHelper = $seo->getPageType($pageType);

            $this->_suffixesByPageType[$pageType] = $this->_getSuffixes($token,
                $this->_addDotToSuffix($pageTypeHelper->getCurrentSuffix()), $pageTypeHelper->getSuffixHistoryType());
        }

        return $this->_suffixesByPageType[$pageType];
    }
    /**
     * @param Mana_Seo_Model_ParsedUrl $token
     * @param string | string[] $current
     * @param string | string[] $type
     * @return string[]
     */
    protected function _getSuffixes($token, $current, $type) {
        /* @var $dbHelper Mana_Db_Helper_Data */
        $dbHelper = Mage::helper('mana_db');

        $suffixes = array();
        if (!is_array($current)) {
            $current = array($current);
        }
        if (!is_array($type)) {
            $type = array($type);
        }
        foreach ($current as $suffix) {
            if ($this->_matchSuffix($token, $suffix)) {
                $suffixes[$suffix] = true;
            }
        }

        /* @var $oldSuffixCollection Mana_Db_Resource_Entity_Collection */
        $oldSuffixCollection = $dbHelper->getResourceModel('mana_seo/urlHistory_collection');
        $oldSuffixCollection->getSelect()
            ->where('type IN (?)', $type)
            ->where('store_id IN(?)', array(0, $this->_storeId))
            ->where('url_key NOT IN (?)', $current);
        foreach ($oldSuffixCollection as $historyRecord) {
            /* @var $historyRecord Mana_Seo_Model_UrlHistory */
            $suffix = $this->_addDotToSuffix((string)$historyRecord->getUrlKey());
            if ($this->_matchSuffix($token, $suffix)) {
                $suffixes[$suffix] = false;
            }
        }
        return $suffixes;
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl[] | bool $tokens
     * @param int $conditions
     * @return Mana_Seo_Model_Url[]
     */
    protected function _getUrls($tokens, $conditions)
    {
        /* @var $dbHelper Mana_Db_Helper_Data */
        $dbHelper = Mage::helper('mana_db');

        /* @var $collection Mana_Seo_Resource_Url_Collection */
        $collection = $dbHelper->getResourceModel('mana_seo/url_collection');
        $collection
            ->addOptionAttributeIdAndCodeToSelect()
            ->addAttributeCodeToSelect()
            ->addManadevFilterTypeToSelect($this->_storeId)
            ->setSchemaFilter($this->_schema)
            ->addFieldToFilter('status', array(
                'in' => array(
                    Mana_Seo_Model_Url::STATUS_ACTIVE,
                    Mana_Seo_Model_Url::STATUS_OBSOLETE
                )
            ));

        if ($tokens !== false) {
            $keys = array();
            foreach (array_keys($tokens) as $key) {
                $keys[] = new Zend_Db_Expr("'$key'");
            }
            $collection->addFieldToFilter('final_url_key', array('in' => $keys));
        }
        $parserConditions = array();
        if ($conditions & self::IS_PAGE) {
            $parserConditions[] = "(`main_table`.`is_page` = 1)";
        }
        if ($conditions & self::IS_PARAMETER) {
            $parserConditions[] = "(`main_table`.`is_parameter` = 1)";
        }
        if ($conditions & self::IS_ATTRIBUTE_VALUE) {
            $parserConditions[] = "(`main_table`.`is_attribute_value` = 1)";
        }
        if ($conditions & self::IS_CATEGORY_VALUE) {
            $parserConditions[] = "(`main_table`.`is_category_value` = 1)";
        }
        if (count($parserConditions)) {
            $collection->getSelect()->where(new Zend_Db_Expr(implode(' OR ', $parserConditions)));
        }
        return $collection;
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl[][] | bool $tokens
     * @return Mana_Seo_Model_ParsedUrl[] | bool
     */
    protected function _getPageUrlKeysAndRemoveSuffixes($tokens) {
        if (!$tokens) {
            return false;
        }
        $result = array();
        $flatResult = array();
        $flatTokens = array();
        foreach ($tokens as $suffix => $suffixTokens) {
            $result[$suffix] = array();
            $flatTokens = array_merge($flatTokens, $suffixTokens);
        }
        foreach ($this->_getUrls($flatTokens, self::IS_PAGE) as $url) {
            foreach ($tokens as $suffix => $suffixTokens){
                if (!isset($result[$suffix][$url->getUrlKey()])) {
                    if (isset($suffixTokens[$url->getUrlKey()])) {
                        /* @var $token Mana_Seo_Model_ParsedUrl */
                        $token = $suffixTokens[$url->getUrlKey()];
                        if (in_array($suffix, $this->_getSuffixesByType($token, $url->getType()))) {
                            $token->setPageUrl($url);
                            $this->_activate($token, $url->getStatus() == Mana_Seo_Model_Url::STATUS_ACTIVE);
                            $flatResult[] = $result[$suffix][$url->getUrlKey()] = $token;
                        }
                    }
                }
                else {
                    $this->_conflict($url->getUrlKey(), self::CONFLICT_PAGE);
                }
            }
        }
        return count($flatResult) ? $flatResult : false;
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl[] $tokens
     * @return Mana_Seo_Model_ParsedUrl[] | bool
     */
    protected function _getAttributeValueUrlKeys($tokens) {
        if (!$tokens) {
            return false;
        }
        $result = array();

        /* @var $urlCollection Mana_Seo_Resource_Url_Collection */
        $urlCollection = $urls = $this->_getUrls($tokens, self::IS_ATTRIBUTE_VALUE);
        /* @var $token Mana_Seo_Model_ParsedUrl */
        $token = current($tokens);
        if (($attributeId = $token->getAttributeId()) !== false) {
            $urlCollection->addOptionAttributeFilter($attributeId);
        }
        foreach ($urls as $url) {
            if (!isset($result[$url->getUrlKey()])) {
                $token = $tokens[$url->getUrlKey()];
                $token->setAttributeValueUrl($url);
                $this->_activate($token, $url->getStatus() == Mana_Seo_Model_Url::STATUS_ACTIVE);
                $result[$url->getUrlKey()] = $token;
            }
            else {
                $this->_conflict($url->getUrlKey(), self::CONFLICT_ATTRIBUTE_VALUE);
            }

        }

        return count($result) ? $result : false;
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl $token
     * @return bool
     */
    protected function _getParameterUrlKey($token) {
        if (!$this->_parameterUrls) {
            $this->_parameterUrls = array();
            foreach ($this->_getUrls(false, self::IS_PARAMETER) as $url) {
                if (!isset($this->_parameterUrls[$url->getUrlKey()])) {
                    $this->_parameterUrls[$url->getUrlKey()] = $url;
                }
                else {
                    $this->_conflict($url->getUrlKey(), self::CONFLICT_PARAMETER);
                }
            }
        }
        if (isset($this->_parameterUrls[$token->getText()])) {
            $token->setParameterUrl($this->_parameterUrls[$token->getText()]);
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl $token
     * @return int | bool
     */
    protected function _getCategoryIdByPageUrlKey($token) {
        return $token->getPageUrl()->getType() == 'category'
            ? $token->getPageUrl()->getCategoryId()
            : Mage::app()->getStore($this->_storeId)->getRootCategoryId();
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl $token
     * @param int $categoryId
     * @return int | bool
     */
    protected function _getCategoryIdByFilterUrlKey($token, $categoryId) {
        /* @var $urlCollection Mana_Seo_Resource_Url_Collection */
        $urlCollection = $urls = $this->_getUrls(array($token->getText() => $token), self::IS_CATEGORY_VALUE);
        $urlCollection->addParentCategoryFilter($categoryId);

        $result = false;
        foreach ($urls as $url) {
            if ($result === false) {
                $result = $url->getCategoryId();
            }
            else {
                $this->_conflict($url->getUrlKey(), self::CONFLICT_CATEGORY_VALUE);
            }
        }
        return $result;
    }

    #endregion

    #region Processing Results

    /**
     * @param Mana_Seo_Model_ParsedUrl $token
     * @return bool
     */
    protected function _setPage($token) {
        /* @var $seo Mana_Seo_Helper_Data */
        $seo = Mage::helper('mana_seo');

        $token->setPageUrlKey($token->getPageUrl()->getUrlKey());
        return $seo->getPageType($token->getPageUrl()->getType())->setPage($token);
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl $token
     * @return bool
     */
    protected function _setHomePage($token) {
        /* @var $seo Mana_Seo_Helper_Data */
        $seo = Mage::helper('mana_seo');

        $token->setPageUrlKey('');
        return $seo->getPageType('home_page')->setPage($token);
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl $token
     * @return bool
     */
    protected function _setAttributeFilter($token) {
        if ($token->getAttributeId() === false) {
            $token
                ->setAttributeId($token->getAttributeValueUrl()->getOptionAttributeId())
                ->setAttributeCode($token->getAttributeValueUrl()->getOptionAttributeCode());
        }
        $token->addParameter($token->getAttributeCode(), $token->getAttributeValueUrl()->getOptionId());

        return true;
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl $token
     * @param int $categoryId
     * @return bool
     */
    protected function _setCategoryFilter($token, $categoryId) {
        $token->addParameter('cat', $categoryId);

        return true;
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl $token
     * @param int $from
     * @param int $to
     * @return bool
     */
    protected function _setPriceFilter($token, $from, $to) {
        /* @var $seo Mana_Seo_Helper_Data */
        $seo = Mage::helper('mana_seo');

        $from = 0 + $from;
        $to = 0 + $to;
        if ($from > $to) {
            $t = $from;
            $from = $to;
            $to = $t;
        }
        if ($seo->isManadevLayeredNavigationInstalled() &&
            in_array($token->getParameterUrl()->getFilterDisplay(), array('slider', 'range')))
        {
            $token->addParameter($token->getAttributeCode(), "$from,$to");

        }
        else {
            if ($from == $to) {
                return false;
            }
            $range = $from - $to;
            $rawIndex = $from / $range;
            $index = round($rawIndex);
            if (abs($index - $rawIndex) >= 0.001) {
                return false;
            }

            $token->addParameter($token->getAttributeCode(), "$index,$range");
        }

        return true;
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl $token
     * @return bool
     */
    protected function _setToolbarValue($token) {
        $token->addParameter($token->getParameterUrl()->getInternalName(), $token->getText());

        return true;
    }


    /**
     * @param Mana_Seo_Model_ParsedUrl $token
     * @return bool
     */
    protected function _setResult($token) {
        if ($token->getStatus() & Mana_Seo_Model_ParsedUrl::STATUS_MASK_CORRECTION
            && $token->getRoute() == 'cms/index/index' && !count($token->getParameters()))
        {
            return false;
        }

        $token
            // unset scanner properties
            ->unsetData('text')->unsetData('text_to_be_parsed')->unsetData('super_text')
            ->unsetData('super_text_to_be_parsed')->unsetData('slash')

            // unset parser properties
            ->unsetData('attribute_id')->unsetData('attribute_code')

            // unset data operation properties
            ->unsetData('page_url')->unsetData('attribute_value_url')->unsetData('parameter_url')

            // unset result processing properties
            ->unsetData('parameter_type');

        if (!isset($this->_results[$token->getStatus()])) {
            $this->_results[$token->getStatus()] = array();
        }
        $this->_results[$token->getStatus()][] = $token;

        // Return if exact match found
        return !$this->_diagnosticMode && $token->getStatus() == Mana_Seo_Model_ParsedUrl::STATUS_OK;
    }

    #endregion

    #region Status

    /**
     * @param Mana_Seo_Model_ParsedUrl $token
     * @param int $correction
     * @param int $line
     * @param string $text
     * @return bool
     */
    protected function _correct($token, $correction, $line, $text) {
        $token
            ->setStatus($token->getStatus() | Mana_Seo_Model_ParsedUrl::STATUS_MASK_CORRECTION)
            ->setTextToBeParsed('')
            ->setSuperTextToBeParsed('');
        $token->addCorrection($correction, $line, $text);
        return count($token->getCorrections()) <= Mage::getStoreConfig('mana/seo/max_correction_count')
            ? true
            : $this->_setResult($token);
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl $token
     * @param bool $active
     */
    protected function _activate($token, $active) {
        $status = $active ? Mana_Seo_Model_ParsedUrl::STATUS_OK : Mana_Seo_Model_ParsedUrl::STATUS_OBSOLETE;
        if (($token->getStatus() & Mana_Seo_Model_ParsedUrl::STATUS_MASK_ACTIVE) < $status) {
            $token->setStatus($status | ($token->getStatus() & Mana_Seo_Model_ParsedUrl::STATUS_MASK_CORRECTION ));
        }
    }

    protected function _conflict($urlKey, $type) {
        $this->_conflicts[] = compact('urlKey', 'type');
    }

    #endregion
    #region Helpers

    /**
     * @param Mana_Seo_Model_ParsedUrl $token
     * @param string $suffix
     * @return bool
     */
    protected function _matchSuffix($token, $suffix) {
        return $this->_internalMatchSuffix($token->getSuffix() ? $token->getSuffix() : $token->getTextToBeParsed(), $token->getSlash(), $suffix);
    }

    /**
     * @param string $text
     * @param string $slash
     * @param string $suffix
     * @return bool
     */
    protected function _internalMatchSuffix($text, $slash, $suffix) {
        if ($suffix) {
            if ($suffix == '/') {
                return $slash == '/';
            }
            else {
                /* @var $mbstring Mana_Core_Helper_Mbstring */
                $mbstring = Mage::helper('mana_core/mbstring');

                return $mbstring->endsWith($text, $suffix);
            }
        }
        else {
            return true;
        }
    }

    protected function _addDotToSuffix($suffix) {
        if ($suffix && $suffix != '/' && strpos($suffix, '.') !== 0) {
            $suffix = '.' . $suffix;
        }
        return $suffix;
    }

    protected function _processResult() {
        /* @var $logger Mana_Core_Helper_Logger */
        $logger = Mage::helper('mana_core/logger');

        $logger->logSeoMatch("---------------------------------------------------");
        $logger->beginSeoMatch("{$this->_path}:");
        $result = false;
        ksort($this->_results);
        foreach ($this->_results as $results) {
            foreach ($results as $parsedUrl) {
                if ($result === false) {
                    $result = $parsedUrl;
                }
                /* @var $parsedUrl Mana_Seo_Model_ParsedUrl */
                $logger->beginSeoMatch("{$parsedUrl->getStatus()} => {$parsedUrl->getPageUrlKey()}|{$parsedUrl->getSuffix()} ({$parsedUrl->getRoute()})");
                if (($parameters = $parsedUrl->getParameters()) && count($parameters)) {
                    foreach ($parameters as $parameter => $values) {
                        $logger->logSeoMatch("$parameter: " . implode(', ', $values));
                    }
                }
                if (($corrections = $parsedUrl->getCorrections()) && count($corrections)) {
                    foreach ($corrections as $correction) {
                        $logger->logSeoMatch("{$parsedUrl->getCorrectionName($correction)
                        } ('{$correction['text']}' on line {$correction['line']})");
                    }
                }
                $logger->endSeoMatch();
            }
        }
        if (count($this->_conflicts)) {
            $logger->beginSeoMatch("Conflicts");
            foreach ($this->_conflicts as $conflict) {
                $logger->logSeoMatch("{$conflict['urlKey']} ({$conflict['type']})");
            }
            $logger->endSeoMatch();
        }
        $logger->endSeoMatch();

        return $result;
    }

    #endregion
}