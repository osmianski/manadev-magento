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

    protected $_allSuffixes = array();

    protected $_suffixesByPageType = array();

    /**
     * @var Mana_Seo_Resource_Url_Collection[]
     */
    protected $_parameterUrls = array();

    protected $_toolbarDirections = array('asc', 'desc');
    protected $_toolbarLimits = array('all');
    protected $_toolbarModes = array('grid', 'list');
    protected $_toolbarOrders;

    public function __construct() {
        if ($additionalToolbarModes = Mage::getStoreConfig('mana/seo/additional_toolbar_modes')) {
            $this->_toolbarModes = array_merge($this->_toolbarModes, explode(',', $additionalToolbarModes));
        }
    }

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

        /* @var $mbstring Mana_Core_Helper_Mbstring */
        $mbstring = Mage::helper('mana_core/mbstring');

        // split by "/", split suffix
        $separator = $this->_schema->getQuerySeparator();
        $suffixes = $this->_scanSuffixes($pageToken, $this->_getAllSuffixes($pageToken));
        $tokens = $this->_scanUntilSeparatorOrSuffix($suffixes, $separator);

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
        if ($mbstring->startsWith($separator, '/') &&
            $mbstring->strlen($separator) > 1 &&
            $mbstring->startsWith($pageToken->getTextToBeParsed(), $mbstring->substr($separator, 1)))
        {
            $pageToken->setTextToBeParsed($mbstring->substr($pageToken->getTextToBeParsed(), $mbstring->strlen($separator) - 1));
        }

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
                        if (!$this->_setCurrentAttribute($token,
                            $token->getParameterUrl()->getAttributeId(),
                            $token->getParameterUrl()->getInternalName())
                        ) {
                            return false;
                        }
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
                        continue;
                    }
                }

                $subToken->setParameterUrl(null);

                if (!$this->_setCurrentAttribute($subToken, false, false)) {
                    return false;
                }
                if ($this->_parseAttributeValues($subToken)) {
                    return true;
                }
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
        $cExpectedParameterName = Mana_Seo_Model_ParsedUrl::CORRECT_EXPECTED_PARAMETER_NAME_FOR_ATTRIBUTE_FILTER_URL_KEY;
        $cRedundantParameterName = Mana_Seo_Model_ParsedUrl::CORRECT_REDUNDANT_PARAMETER_NAME_FOR_ATTRIBUTE_FILTER_URL_KEY;

        // split by "-", add correction for token beginning with "-"
        if (($text = $token->getTextToBeParsed()) && ($tokens = $this->_scanUntilSeparator($token, $this->_schema->getMultipleValueSeparator()))) {
            // get all valid attribute value URL keys
            if ($tokens = $this->_getAttributeValueUrlKeys($tokens)) {
                foreach ($tokens as $token) {
                    if ($token->getAttributeValueUrl()->getFinalIncludeFilterName()) {
                        $token->setPendingCorrection($cExpectedParameterName);
                        if (!$token->getParameterUrl()) {
                            if (!$this->_correct($token, $cExpectedParameterName, __LINE__, $text)) {
                                return false;
                            }
                        }
                    }
                    else {
                        if ($token->getParameterUrl()) {
                            $token->setPendingCorrection($cRedundantParameterName);
                        }
                    }
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

        $token->zoomOut();
        if ($token->hasPendingCorrection($cRedundantParameterName) && !$token->hasPendingCorrection($cExpectedParameterName)) {
            if (!$this->_correct($token, $cRedundantParameterName, __LINE__, $text)) {
                return false;
            }
        }
        $token
            ->clearPendingCorrection($cRedundantParameterName)
            ->clearPendingCorrection($cExpectedParameterName);

        return $this->_parseParameters($token);
    }

    /**
     * CategoryValues ::= Value {"/" Value } .
     * @param Mana_Seo_Model_ParsedUrl $token
     * @return bool
     */
    protected function _parseCategoryValues($token) {
        $cNotFound = Mana_Seo_Model_ParsedUrl::CORRECT_NOT_FOUND_CATEGORY_FILTER_URL_KEY;

        /* @var $seo Mana_Seo_Helper_Data */
        $seo = Mage::helper('mana_seo');

        // get category id defined by page URL key, or store root category is for non category pages
        if (!$token->getCategoryPath()) {
            $token
                ->setCategoryId($this->_getCategoryIdByPageUrlKey($token))
                ->setCategoryPath($seo->getCategoryPath($token->getCategoryId()));
        }

        if (($text = $token->getTextToBeParsed()) && ($tokens = $this->_scanUntilSeparator($token, $this->_schema->getCategorySeparator()))) {
            // get all valid attribute value URL keys
            if ($tokens = $this->_getCategoryValueUrlKeys($tokens)) {
                foreach ($tokens as $token) {
                    // read the rest values. Return if exact match found
                    if ($this->_parseCategoryValues($token)) {
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

        $this->_setCategoryFilter($token, $token->getCategoryId());

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
        if (!$this->_setToolbarValue($token)) {
            return false;
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
                ->setTextToBeParsed($suffix != '/' || $mbstring->endsWith($text, '/')
                    ? $mbstring->substr($text, 0, $mbstring->strlen($text) - $mbstring->strlen($suffix))
                    : $text);

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
                if ($nextPos < $pos) {
                    break;
                }
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
                    $tokens[$this->unaccent($clonedToken->getText())] = $clonedToken;
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
                $tokens[$this->unaccent($clonedToken->getText())] = $clonedToken;

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
            if (($nextPos = $mbstring->strpos($text, $minusSymbol, $pos)) === $pos) {
                $minusText = $minusSymbol;
                $pos += $minusLength;
            }
            else {
                $minusText = '';
            }

            // reading from value
            if (($nextPos = $mbstring->strpos($text, $priceSeparator, $pos)) !== false) {
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
            if (($nextPos = $mbstring->strpos($text, $minusSymbol, $pos)) === $pos) {
                $minusText = $minusSymbol;
                $pos += $minusLength;
            }
            else {
                $minusText = '';
            }

            // reading from value
            if (($nextPos = $mbstring->strpos($text, $multipleValueSeparator, $pos)) !== false) {
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
        if (!isset($this->_allSuffixes[$token->getTextToBeParsed()])) {
            /* @var $seo Mana_Seo_Helper_Data */
            $seo = Mage::helper('mana_seo');

            /* @var $core Mana_Core_Helper_Data */
            $core = Mage::helper('mana_core');

            $current = array();
            $historyType = array();
            foreach ($seo->getPageTypes() as $pageType) {
                $suffix = (string)$pageType->getCurrentSuffix();
                $type = $pageType->getSuffixHistoryType();
                $current[$suffix] = $core->addDotToSuffix($suffix);
                $historyType[$type] = $type;
            }

            $this->_allSuffixes[$token->getTextToBeParsed()] = $this->_getSuffixes($token, $current, $historyType);
        }
        return $this->_allSuffixes[$token->getTextToBeParsed()];
    }

    protected function _getSuffixesByType($token, $pageType) {
        if (!isset($this->_suffixesByPageType[$pageType])) {
            /* @var $seo Mana_Seo_Helper_Data */
            $seo = Mage::helper('mana_seo');

            /* @var $core Mana_Core_Helper_Data */
            $core = Mage::helper('mana_core');

            $pageTypeHelper = $seo->getPageType($pageType);

            $this->_suffixesByPageType[$pageType] = $this->_getSuffixes($token,
                $core->addDotToSuffix($pageTypeHelper->getCurrentSuffix()), $pageTypeHelper->getSuffixHistoryType());
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

        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper('mana_core');

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
            $suffix = $core->addDotToSuffix((string)$historyRecord->getUrlKey());
            if ($this->_matchSuffix($token, $suffix)) {
                $suffixes[$suffix] = false;
            }
        }
        return $suffixes;
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl[] | bool $tokens
     * @param int $type
     * @return Mana_Seo_Model_Url[]
     */
    protected function _getUrls($tokens, $type)
    {
        /* @var $dbHelper Mana_Db_Helper_Data */
        $dbHelper = Mage::helper('mana_db');

        /* @var $collection Mana_Seo_Resource_Url_Collection */
        $collection = $dbHelper->getResourceModel('mana_seo/url_collection');
        $select = $collection->getSelect();
        $connection = $collection->getConnection();

        $accentSensitive = $this->_schema->getData('accent_insensitive')
            ? ''
            : ' COLLATE utf8_bin';
        $collection
            ->setSchemaFilter($this->_schema)
            ->addTypeFilter($type)
            ->addFieldToFilter('status', array(
                'in' => array(
                    Mana_Seo_Model_Url::STATUS_ACTIVE,
                    Mana_Seo_Model_Url::STATUS_OBSOLETE
                )
            ));
        $select->order('status');
        if ($tokens !== false) {
            $keys = array();
            foreach (array_keys($tokens) as $key) {
                $keys[] = $connection->quoteInto("(main_table.final_url_key = ?{$accentSensitive})", is_numeric($key)
                    ? new Zend_Db_Expr("'$key'")
                    : new Zend_Db_Expr($connection->quote($key)));
            }
            //$collection->addFieldToFilter('final_url_key', array('in' => $keys));
            $select->where(implode(' OR ', $keys));

        }
        return $collection;
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl[][] | bool $tokens
     * @return Mana_Seo_Model_ParsedUrl[] | bool
     */
    protected function _getPageUrlKeysAndRemoveSuffixes(&$tokens) {
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
        foreach ($this->_getUrls($flatTokens, Mana_Seo_Resource_Url_Collection::TYPE_PAGE) as $url) {
            foreach ($tokens as $suffix => $suffixTokens){
                if (!isset($result[$suffix][$url->getFinalUrlKey()])) {
                    if (isset($suffixTokens[$url->getFinalUrlKey()])) {
                        /* @var $token Mana_Seo_Model_ParsedUrl */
                        $token = $suffixTokens[$url->getFinalUrlKey()];
                        $suffixStatuses = $this->_getSuffixesByType($token, $url->getType());
                        if (isset($suffixStatuses[$suffix])) {
                            $token->setPageUrl($url);
                            $this->_activate($token, $suffixStatuses[$suffix] && $url->getStatus() == Mana_Seo_Model_Url::STATUS_ACTIVE);
                            $flatResult[] = $result[$suffix][$url->getFinalUrlKey()] = $token;
                        }
                    }
                }
                else {
                    $this->_conflict($url->getFinalUrlKey(), self::CONFLICT_PAGE);
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
        $urlCollection = $urls = $this->_getUrls($tokens, Mana_Seo_Resource_Url_Collection::TYPE_ATTRIBUTE_VALUE);
        /* @var $token Mana_Seo_Model_ParsedUrl */
        $token = current($tokens);
        if (($attributeId = $token->getAttributeId()) !== false) {
            $urlCollection->addFieldToFilter('attribute_id', $attributeId);
        }
        foreach ($urls as $url) {
            $finalUrlKey = $this->unaccent($url->getFinalUrlKey());
            if (isset($result[$finalUrlKey])) {
                /* @var $conflictingToken Mana_Seo_Model_ParsedUrl */
                $conflictingToken = $result[$finalUrlKey];
                if ($url->getStatus() == Mana_Seo_Model_Url::STATUS_ACTIVE && $conflictingToken->getAttributeValueUrl()->getFinalIncludeFilterName()) {
                    unset($result[$finalUrlKey]);
                }
            }
            if (!isset($result[$finalUrlKey])) {
                $token = $tokens[$finalUrlKey];
                $token->setAttributeValueUrl($url);
                $this->_activate($token, $url->getStatus() == Mana_Seo_Model_Url::STATUS_ACTIVE);
                $result[$finalUrlKey] = $token;
            }
            elseif ($url->getStatus() == Mana_Seo_Model_Url::STATUS_ACTIVE && !$url->getFinalIncludeFilterName()) {
                $this->_conflict($url->getFinalUrlKey(), self::CONFLICT_ATTRIBUTE_VALUE);
            }

        }

        return count($result) ? $result : false;
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl $token
     * @return bool
     */
    protected function _getParameterUrlKey($token) {
        if (!isset($this->_parameterUrls[$this->_schema->getId()])) {
            $urls = array();
            foreach ($this->_getUrls(false, Mana_Seo_Resource_Url_Collection::TYPE_PARAMETER) as $url) {
                if (!isset($urls[$url->getFinalUrlKey()])) {
                    $urls[$url->getFinalUrlKey()] = $url;
                }
                else {
                    $this->_conflict($url->getFinalUrlKey(), self::CONFLICT_PARAMETER);
                }
            }
            $this->_parameterUrls[$this->_schema->getId()] = $urls;
        }
        if (isset($this->_parameterUrls[$this->_schema->getId()][$token->getText()])) {
            $token->setParameterUrl($this->_parameterUrls[$this->_schema->getId()][$token->getText()]);
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
        return $token->getPageUrl() && $token->getPageUrl()->getType() == 'category'
            ? $token->getPageUrl()->getCategoryId()
            : Mage::app()->getStore($this->_storeId)->getRootCategoryId();
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl[] $tokens
     * @return Mana_Seo_Model_ParsedUrl[] | bool
     */
    protected function _getCategoryValueUrlKeys($tokens) {
        if (!$tokens) {
            return false;
        }
        $result = array();

        /* @var $urlCollection Mana_Seo_Resource_Url_Collection */
        $urlCollection = $urls = $this->_getUrls($tokens, Mana_Seo_Resource_Url_Collection::TYPE_CATEGORY_VALUE);
        /* @var $token Mana_Seo_Model_ParsedUrl */
        $token = current($tokens);
        $urlCollection->addParentCategoryFilter($token->getCategoryPath());

        foreach ($urls as $url) {
            $finalUrlKey = $this->unaccent($url->getFinalUrlKey());
            if (isset($result[$finalUrlKey])) {
                /* @var $conflictingToken Mana_Seo_Model_ParsedUrl */
                $conflictingToken = $result[$finalUrlKey];
                if ($conflictingToken->getAttributeValueUrl()->getFinalIncludeFilterName()) {
                    unset($result[$finalUrlKey]);
                }
            }
            if (!isset($result[$finalUrlKey])) {
                $token = $tokens[$finalUrlKey];
                $token->setCategoryId($url->getCategoryId());
                $token->setCategoryPath($token->getCategoryPath().'/'. $url->getCategoryId());
                $this->_activate($token, $url->getStatus() == Mana_Seo_Model_Url::STATUS_ACTIVE);
                $result[$finalUrlKey] = $token;
            }
            elseif (!$url->getFinalIncludeFilterName()) {
                $this->_conflict($url->getFinalUrlKey(), self::CONFLICT_ATTRIBUTE_VALUE);
            }

        }

        return count($result) ? $result : false;
    }

    /**
     * @return string[]
     */
    protected function _getAvailableToolbarOrders() {
        if (!$this->_toolbarOrders) {
            /* @var $res Mage_Core_Model_Resource */
            $res = Mage::getSingleton('core/resource');

            /* @var $db Varien_Db_Adapter_Pdo_Mysql */
            $db = $res->getConnection('read');

            $this->_toolbarOrders = $db->fetchCol($db->select()
                ->from(array('a' => $res->getTableName('eav/attribute')), 'attribute_code')
                ->joinInner(array('ca' => $res->getTableName('catalog/eav_attribute')),
                    "`ca`.`attribute_id` = `a`.`attribute_id` AND `ca`.`used_for_sort_by` = 1", null));
            $this->_toolbarOrders[] = 'position';
            $this->_toolbarOrders[] = 'relevance';
            if ($additionalToolbarOrders = Mage::getStoreConfig('mana/seo/additional_toolbar_orders')) {
                $this->_toolbarOrders = array_merge($this->_toolbarOrders, explode(',', $additionalToolbarOrders));
            }

            $obj = new Varien_Object();
            $obj->setData('orders', $this->_toolbarOrders);
            Mage::dispatchEvent('m_toolbar_orders', compact('obj'));
            $this->_toolbarOrders = $obj->getData('orders');

        }
        return $this->_toolbarOrders;
    }

    protected function _getAvailableToolbarDirections() {
        return $this->_toolbarDirections;
    }

    protected function _getAvailableToolbarLimits() {
        return $this->_toolbarLimits;
    }

    protected function _getAvailableToolbarModes() {
        return $this->_toolbarModes;
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

        $token->setPageUrlKey($token->getPageUrl()->getFinalUrlKey());
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
            if (!$this->_setCurrentAttribute($token,
                $token->getAttributeValueUrl()->getAttributeId(),
                $token->getAttributeValueUrl()->getInternalName()))
            {
                return false;
            }
        }
        $token->addQueryParameter($token->getAttributeCode(), $token->getAttributeValueUrl()->getOptionId());

        return true;
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl $token
     * @param $id
     * @param $code
     * @return bool
     */
    protected function _setCurrentAttribute($token, $id, $code) {
        $cParameterAlreadyMet = Mana_Seo_Model_ParsedUrl::CORRECT_PARAMETER_ALREADY_MET;
        $token->setAttributeId($id)->setAttributeCode($code);
        if ($code && $token->hasQueryParameter($code)) {
            return $this->_correct($token, $cParameterAlreadyMet, __LINE__, $code);
        }
        return true;
    }
    /**
     * @param Mana_Seo_Model_ParsedUrl $token
     * @param int $categoryId
     * @return bool
     */
    protected function _setCategoryFilter($token, $categoryId) {
        $cParameterAlreadyMet = Mana_Seo_Model_ParsedUrl::CORRECT_PARAMETER_ALREADY_MET;
        $cRedirectToSubcategory = Mana_Seo_Model_ParsedUrl::CORRECT_REDIRECT_TO_SUBCATEGORY;

        if ($this->_schema->getRedirectToSubcategory() && $token->getIsRedirectToSubcategoryPossible()) {
            $token
                ->removeParameter('id')
                ->addParameter('id', $categoryId);
            return $this->_redirect($token, $cRedirectToSubcategory, __LINE__, $token->getText());
        }
        if ($token->hasQueryParameter('cat')) {
            return $this->_correct($token, $cParameterAlreadyMet, __LINE__, 'cat');
        }
        $token->addQueryParameter('cat', $categoryId);

        return true;
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl $token
     * @param int $from
     * @param int $to
     * @return bool
     */
    protected function _setPriceFilter($token, $from, $to) {
        $cSwapRangeBounds = Mana_Seo_Model_ParsedUrl::CORRECT_SWAP_RANGE_BOUNDS;

        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper('mana_core');

        $isSlider = $core->isManadevLayeredNavigationInstalled() &&
            in_array($token->getParameterUrl()->getFilterDisplay(), array('slider', 'range', 'min_max_slider'));
        if ($this->_schema->getUseRangeBounds() || $isSlider) {
            $from = 0 + $from;
            $to = 0 + $to;
            if ($from > $to) {
                $this->_notice($token, $cSwapRangeBounds, __LINE__, "$from,$to");
                $t = $from;
                $from = $to;
                $to = $t;
            }
            if ($isSlider) {
                $token->addQueryParameter($token->getAttributeCode(), "$from,$to");

            }
            else {
                if ($from == $to) {
                    return false;
                }
                $range = $to - $from;
                $rawIndex = $to / $range;
                $index = round($rawIndex);
                if (abs($index - $rawIndex) >= 0.001) {
                    return false;
                }

                $token->addQueryParameter($token->getAttributeCode(), "$index,$range");
            }
        }
        else {
            $token->addQueryParameter($token->getAttributeCode(), "$from,$to");
        }



        return true;
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl $token
     * @return bool
     */
    protected function _setToolbarValue($token) {
        $cParameterAlreadyMet = Mana_Seo_Model_ParsedUrl::CORRECT_PARAMETER_ALREADY_MET;
        $cInvalid = Mana_Seo_Model_ParsedUrl::CORRECT_INVALID_TOOLBAR_VALUE;

        if ($token->hasQueryParameter($token->getParameterUrl()->getInternalName())) {
            return $this->_correct($token, $cParameterAlreadyMet, __LINE__, $token->getParameterUrl()->getInternalName());
        }
        $value = $token->getTextToBeParsed();
        switch ($name = $token->getParameterUrl()->getInternalName()) {
            case 'p':
                if (!is_numeric($value)) {
                    if (!$this->_correct($token, $cInvalid, __LINE__, $token->getText())) {
                        return false;
                    }
                }
                break;
            case 'order':
                if (!in_array($value, $this->_getAvailableToolbarOrders())) {
                    if (!$this->_correct($token, $cInvalid, __LINE__, $token->getText())) {
                        return false;
                    }
                }
                break;
            case 'dir':
                if (!in_array($value, $this->_getAvailableToolbarDirections())) {
                    if (!$this->_correct($token, $cInvalid, __LINE__, $token->getText())) {
                        return false;
                    }
                }
                break;
            case 'limit':
                if (!is_numeric($value) && !in_array($value, $this->_getAvailableToolbarLimits())) {
                    if (!$this->_correct($token, $cInvalid, __LINE__, $token->getText())) {
                        return false;
                    }
                }
                break;
            case 'mode':
                if (!in_array($value, $this->_getAvailableToolbarModes())) {
                    if (!$this->_correct($token, $cInvalid, __LINE__, $token->getText())) {
                        return false;
                    }
                }
                break;
        }
        $token->addQueryParameter($name, $token->getTextToBeParsed());

        return true;
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl $token
     * @return bool
     */
    protected function _setResult($token) {
        if ($token->getStatus() & Mana_Seo_Model_ParsedUrl::STATUS_MASK_CORRECTION
            && $token->getRoute() == 'cms/index/index' && !count($token->getQueryParameters()))
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
     * @param $mask
     * @return bool
     */
    protected function _internalCorrect($token, $correction, $line, $text, $mask) {
        $token
            ->setStatus($token->getStatus() | $mask)
            ->setTextToBeParsed('')
            ->setSuperTextToBeParsed('');
        $token->addCorrection($correction, $line, $text);

        $count = 0;
        foreach ($token->getCorrections() as $correction) {
            if ($correction['correction'] & Mana_Seo_Model_ParsedUrl::STATUS_MASK_COUNTED) {
                $count++;
            }
        }
        return $count <= Mage::getStoreConfig('mana/seo/max_correction_count')
            ? true
            : $this->_setResult($token);
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl $token
     * @param int $correction
     * @param int $line
     * @param string $text
     * @return bool
     */
    protected function _correct($token, $correction, $line, $text) {
        return $this->_internalCorrect($token, $correction, $line, $text,
            Mana_Seo_Model_ParsedUrl::STATUS_MASK_CORRECTION);
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl $token
     * @param int $correction
     * @param int $line
     * @param string $text
     * @return bool
     */
    protected function _notice($token, $correction, $line, $text) {
        return $this->_internalCorrect($token, $correction, $line, $text,
            Mana_Seo_Model_ParsedUrl::STATUS_MASK_NOTICE);
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl $token
     * @param int $correction
     * @param int $line
     * @param string $text
     * @return bool
     */
    protected function _redirect($token, $correction, $line, $text) {
        return $this->_internalCorrect($token, $correction, $line, $text,
            Mana_Seo_Model_ParsedUrl::STATUS_MASK_REDIRECT);
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl $token
     * @param bool $active
     */
    protected function _activate($token, $active) {
        $status = $active ? Mana_Seo_Model_ParsedUrl::STATUS_OK : Mana_Seo_Model_ParsedUrl::STATUS_OBSOLETE;
        if (($token->getStatus() & Mana_Seo_Model_ParsedUrl::STATUS_MASK_ACTIVE) < $status) {
            $token->setStatus($status | ($token->getStatus() & ~Mana_Seo_Model_ParsedUrl::STATUS_MASK_ACTIVE ));
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

    protected function _processResult() {
        /* @var $logger Mana_Core_Helper_Logger */
        $logger = Mage::helper('mana_core/logger');

        $logger->logSeoMatch("---------------------------------------------------");
        $logger->beginSeoMatch("{$this->_path}:");
        $result = false;
        ksort($this->_results);
        foreach ($this->_results as $results) {
            usort($results, array($this, '_compareResults'));
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
                if (($parameters = $parsedUrl->getQueryParameters()) && count($parameters)) {
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

    /**
     * @param Mana_Seo_Model_ParsedUrl $a
     * @param Mana_Seo_Model_ParsedUrl $b
     * @return int
     */
    protected function _compareResults($a, $b) {
        $aCount = count($a->getQueryParameters());
        $bCount = count($b->getQueryParameters());

        if ($aCount < $bCount) return 1;
        if ($aCount > $bCount) return -1;

        $aCount = 0;
        foreach ($a->getQueryParameters() as $values) {
            $aCount += count($values);
        }
        $bCount = 0;
        foreach ($b->getQueryParameters() as $values) {
            $bCount += count($values);
        }

        if ($aCount < $bCount) return 1;
        if ($aCount > $bCount) return -1;

        return 0;
    }

    #endregion

    #region Test Helpers
    public function clearParameterUrlCache() {
        $this->_parameterUrls = array();
        return $this;
    }

    #endregion

    #region Dependencies

    /**
     * @return Mana_Core_Helper_Data
     */
    public function coreHelper() {
        return Mage::helper('mana_core');
    }

    /**
     * @return Mana_Core_Helper_Logger
     */
    public function logger() {
        return Mage::helper('mana_core/logger');
    }

    protected function unaccent($s) {
        return $this->_schema->getData('accent_insensitive')
            ? $this->coreHelper()->unaccent($s)
            : $s;
    }
    #endregion
}