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
 * Result properties
 *
 * @method string getStatus()
 * @method Mana_Seo_Model_ParsedUrl setStatus(string $value)
 * @method string getPageUrlKey()
 * @method Mana_Seo_Model_ParsedUrl setPageUrlKey(string $value)
 * @method string getRoute()
 * @method Mana_Seo_Model_ParsedUrl setRoute(string $value)
 * @method string getSuffix()
 * @method Mana_Seo_Model_ParsedUrl setSuffix(string $value)
 *
 * Scanner properties
 *
 * @method string getText() currently analyzed parsed URL string
 * @method Mana_Seo_Model_ParsedUrl setText(string $value)
 * @method string getTextToBeParsed() URL string to be parsed
 * @method Mana_Seo_Model_ParsedUrl setTextToBeParsed(string $value)
 * @method string getSuperText() currently analyzed parsed URL string
 * @method Mana_Seo_Model_ParsedUrl setSuperText(string $value)
 * @method string getSuperTextToBeParsed() URL string to be parsed
 * @method Mana_Seo_Model_ParsedUrl setSuperTextToBeParsed(string $value)
 * @method string getSlash() trailing right slash (or empty string if none)
 * @method Mana_Seo_Model_ParsedUrl setSlash(string $value)
 *
 * Parser properties
 *
 * @method int | bool getAttributeId() - id of attribute of which values are currently expected
 * @method Mana_Seo_Model_ParsedUrl setAttributeId(mixed $value)
 * @method string | bool getAttributeCode() - code of attribute of which values are currently expected
 * @method Mana_Seo_Model_ParsedUrl setAttributeCode(mixed $value)
 * @method bool getIsRedirectToSubcategoryPossible() - code of attribute of which values are currently expected
 * @method Mana_Seo_Model_ParsedUrl setIsRedirectToSubcategoryPossible(bool $value)
 * @method int getCategoryId() - id of category of which children category URL keys are currently expected
 * @method Mana_Seo_Model_ParsedUrl setCategoryId(int $value)
 * @method string getCategoryPath() - current category ID path
 * @method Mana_Seo_Model_ParsedUrl setCategoryPath(string $value)
 *
 * Data operation properties
 *
 * @method Mana_Seo_Model_Url getPageUrl() - URL index entry for page URL key. Not set for CMS home page. Unset after page info is set
 * @method Mana_Seo_Model_ParsedUrl setPageUrl(Mana_Seo_Model_Url $value)
 * @method Mana_Seo_Model_Url getAttributeValueUrl() - URL index entry for current attribute value URL key. Unset after parameter value is registered
 * @method Mana_Seo_Model_ParsedUrl setAttributeValueUrl(Mana_Seo_Model_Url $value)
 * @method Mana_Seo_Model_Url getParameterUrl() - URL index entry for current parameter URL key. Unset after parameter value is registered
 * @method Mana_Seo_Model_ParsedUrl setParameterUrl(Mana_Seo_Model_Url $value)
 *
 * Result processing properties
 * @method int getParameterType() type of named parameter, one of PARAMETER_ constants
 * @method Mana_Seo_Model_ParsedUrl setParameterType(int $value)
 */
class Mana_Seo_Model_ParsedUrl extends Varien_Object {
    const STATUS_MASK_ACTIVE = 0x03;
    const STATUS_MASK_NOTICE = 0x10;
    const STATUS_MASK_REDIRECT = 0x20;
    const STATUS_MASK_CORRECTION = 0x40;
    const STATUS_MASK_COUNTED = 0x60;
    const STATUS_OK = 0x01;
    const STATUS_OBSOLETE = 0x02;
    const STATUS_NOTICE = 0x11;
    const STATUS_OBSOLETE_NOTICE = 0x12;
    const STATUS_REDIRECT = 0x21;
    const STATUS_OBSOLETE_REDIRECT = 0x22;
    const STATUS_CORRECTION = 0x41;
    const STATUS_OBSOLETE_CORRECTION = 0x042;

    const PARAMETER_ATTRIBUTE = 'attribute';
    const PARAMETER_CATEGORY = 'category';
    const PARAMETER_PRICE = 'price';
    const PARAMETER_TOOLBAR = 'toolbar';

    const CORRECT_NOT_FOUND_CATEGORY_FILTER_URL_KEY = 0x0001;
    const CORRECT_NOT_FOUND_ATTRIBUTE_FILTER_URL_KEY = 0x0002;
    const CORRECT_INVALID_PRICE_FILTER_VALUE = 0x0004;
    const CORRECT_INVALID_TOOLBAR_VALUE = 0x0008;
    const CORRECT_EXPECTED_PARAMETER_NAME_FOR_ATTRIBUTE_FILTER_URL_KEY = 0x0010;
    const CORRECT_REDUNDANT_PARAMETER_NAME_FOR_ATTRIBUTE_FILTER_URL_KEY = 0x0020;
    const CORRECT_PARAMETER_ALREADY_MET = 0x0040;
    const CORRECT_REDIRECT_TO_SUBCATEGORY = 0x0080;
    const CORRECT_SWAP_RANGE_BOUNDS = 0x0100;

    protected static $_correctionNames = array(
        self::CORRECT_NOT_FOUND_CATEGORY_FILTER_URL_KEY => 'CORRECT_NOT_FOUND_CATEGORY_FILTER_URL_KEY',
        self::CORRECT_NOT_FOUND_ATTRIBUTE_FILTER_URL_KEY => 'CORRECT_NOT_FOUND_ATTRIBUTE_FILTER_URL_KEY',
        self::CORRECT_INVALID_PRICE_FILTER_VALUE => 'CORRECT_INVALID_PRICE_FILTER_VALUE',
        self::CORRECT_INVALID_TOOLBAR_VALUE => 'CORRECT_INVALID_TOOLBAR_VALUE',
        self::CORRECT_EXPECTED_PARAMETER_NAME_FOR_ATTRIBUTE_FILTER_URL_KEY => 'CORRECT_EXPECTED_PARAMETER_NAME_FOR_ATTRIBUTE_FILTER_URL_KEY',
        self::CORRECT_REDUNDANT_PARAMETER_NAME_FOR_ATTRIBUTE_FILTER_URL_KEY => 'CORRECT_REDUNDANT_PARAMETER_NAME_FOR_ATTRIBUTE_FILTER_URL_KEY',
        self::CORRECT_PARAMETER_ALREADY_MET => 'CORRECT_PARAMETER_ALREADY_MET',
        self::CORRECT_REDIRECT_TO_SUBCATEGORY => 'CORRECT_REDIRECT_TO_SUBCATEGORY',
        self::CORRECT_SWAP_RANGE_BOUNDS => 'CORRECT_SWAP_RANGE_BOUNDS',
    );
    protected $_parameters = array();
    protected $_queryParameters = array();
    protected $_corrections = array();
    protected $_pendingCorrections = 0;
    #region Parsed URL parameters

    /**
     * @param string $key
     * @param mixed $value
     * @return Mana_Seo_Model_ParsedUrl
     */
    public function addParameter($key, $value) {
        if (!isset($this->_parameters[$key])) {
            $this->_parameters[$key] = array();
        }
        $this->_parameters[$key][] = $value;
        return $this;
    }

    /**
     * @param string $key
     * @return Mana_Seo_Model_ParsedUrl
     */
    public function removeParameter($key) {
        unset($this->_parameters[$key]);

        return $this;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasParameter($key) {
        return isset($this->_parameters[$key]);
    }

    public function getParameter($key) {
        return $this->_parameters[$key];
    }

    public function getParameters() {
        return $this->_parameters;
    }

    public function getImplodedParameters() {
        return $this->_implode($this->_parameters);
    }

    #endregion

    #region Parsed URL query parameters

    /**
     * @param string $key
     * @param mixed $value
     * @return Mana_Seo_Model_ParsedUrl
     */
    public function addQueryParameter($key, $value) {
        if (!isset($this->_queryParameters[$key])) {
            $this->_queryParameters[$key] = array();
        }
        $this->_queryParameters[$key][] = $value;

        return $this;
    }

    /**
     * @param string $key
     * @return Mana_Seo_Model_ParsedUrl
     */
    public function removeQueryParameter($key) {
        unset($this->_queryParameters[$key]);

        return $this;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasQueryParameter($key) {
        return isset($this->_queryParameters[$key]);
    }

    public function getQueryParameter($key) {
        return $this->_queryParameters[$key];
    }

    public function getQueryParameters() {
        return $this->_queryParameters;
    }

    public function getImplodedQueryParameters() {
        return $this->_implode($this->_queryParameters);
    }

    #endregion

    public function zoomIn() {
        $result = clone $this;
        return $result
            ->setSuperText($this->getText())
            ->setSuperTextToBeParsed($this->getTextToBeParsed())
            ->setTextToBeParsed($this->getText())
            ->setText('');
    }

    public function zoomOut() {
        return $this
            ->setTextToBeParsed($this->getSuperTextToBeParsed())
            ->setText($this->getSuperText())
            ->setSuperText('')
            ->setSuperTextToBeParsed('');
    }

    public function addCorrection($correction, $line, $text) {
        $this->_corrections[] = compact('correction', 'line', 'text');
        return $this;
    }

    public function getCorrections() {
        return $this->_corrections;
    }

    public function getCorrectionName($correction) {
        return self::$_correctionNames[$correction['correction']];
    }

    public function setPendingCorrection($correction) {
        $this->_pendingCorrections |= $correction;
        return $this;
    }

    public function clearPendingCorrection($correction) {
        $this->_pendingCorrections &= ~$correction;
        return $this;;
    }

    public function hasPendingCorrection($correction) {
        return ($this->_pendingCorrections & $correction) == $correction;
    }

    protected function _implode($parameters) {
        $result = array();
        foreach ($parameters as $key => $value) {
            $result[$key] = implode('_', $value);
        }
        return $result;
    }
}