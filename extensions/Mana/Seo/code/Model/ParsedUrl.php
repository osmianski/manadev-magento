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
    const STATUS_OK = 1;
    const STATUS_OBSOLETE = 2;
    const STATUS_CORRECTION = 5;
    const STATUS_OBSOLETE_CORRECTION = 6;
    const STATUS_MASK_ACTIVE = 3;
    const STATUS_MASK_CORRECTION = 4;

    const PARAMETER_ATTRIBUTE = 'attribute';
    const PARAMETER_CATEGORY = 'category';
    const PARAMETER_PRICE = 'price';
    const PARAMETER_TOOLBAR = 'toolbar';

    const CORRECT_NOT_FOUND_CATEGORY_FILTER_URL_KEY = 0x0001;
    const CORRECT_NOT_FOUND_ATTRIBUTE_FILTER_URL_KEY = 0x0002;
    const CORRECT_INVALID_PRICE_FILTER_VALUE = 0x0004;
    const CORRECT_INVALID_TOOLBAR_VALUE = 0x0008;

    protected static $_correctionNames = array(
        self::CORRECT_NOT_FOUND_CATEGORY_FILTER_URL_KEY => 'CORRECT_NOT_FOUND_CATEGORY_FILTER_URL_KEY',
        self::CORRECT_NOT_FOUND_ATTRIBUTE_FILTER_URL_KEY => 'CORRECT_NOT_FOUND_ATTRIBUTE_FILTER_URL_KEY',
        self::CORRECT_INVALID_PRICE_FILTER_VALUE => 'CORRECT_INVALID_PRICE_FILTER_VALUE',
        self::CORRECT_INVALID_TOOLBAR_VALUE => 'CORRECT_INVALID_TOOLBAR_VALUE',
    );
    protected $_parameters = array();
    protected $_corrections = array();

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
}