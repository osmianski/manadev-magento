<?php
/** 
 * @category    Mana
 * @package     Mana_Seo
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 * @method string getQuerySeparator() '/' in 'url/p1-v1_v2/p2-v3_v4_v5.html'
 * @method Mana_Seo_Model_Schema setQuerySeparator(string $value)
 * @method string getParamSeparator() '/' in 'url/p1-v1_v2/p2-v3_v4_v5.html'
 * @method Mana_Seo_Model_Schema setParamSeparator(string $value)
 * @method string getFirstValueSeparator() '-' in 'url/p1-v1_v2/p2-v3_v4_v5.html'
 * @method Mana_Seo_Model_Schema setFirstValueSeparator(string $value)
 * @method string getMultipleValueSeparator() '_' in 'url/p1-v1_v2/p2-v3_v4_v5.html'
 * @method Mana_Seo_Model_Schema setMultipleValueSeparator(string $value)
 * @method string getStatus()
 * @method Mana_Seo_Model_Schema setStatus(string $value)
 * @method int getUseFilterLabels()
 * @method Mana_Seo_Model_Schema setUseFilterLabels(int $value)
 * @method string getSymbols()
 * @method Mana_Seo_Model_Schema setSymbols(string $value)
 * @method string getToolbarUrlKeys()
 * @method Mana_Seo_Model_Schema setToolbarUrlKeys(string $value)
 * @method string getName()
 * @method Mana_Seo_Model_Schema setName(string $value)
 * @method string getInternalName()
 * @method Mana_Seo_Model_Schema setInternalName(string $value)
 * @method bool getRedirectParameterOrder()
 * @method Mana_Seo_Model_Schema setRedirectParameterOrder(bool $value)
 * @method string getIncludeFilterName()
 * @method Mana_Seo_Model_Schema setIncludeFilterName(string $value)
 * @method bool getRedirectToSubcategory()
 * @method Mana_Seo_Model_Schema setRedirectToSubcategory(bool $value)
 * @method bool getRedirectToOptionPage()
 * @method Mana_Seo_Model_Schema setRedirectToOptionPage(bool $value)
 */
class Mana_Seo_Model_Schema extends Mana_Db_Model_Entity {
    const STATUS_ACTIVE = 'active';
    const STATUS_OBSOLETE = 'obsolete';
    const STATUS_DISABLED = 'disabled';

    const INCLUDE_NEVER = 'never';
    const INCLUDE_ALWAYS = 'always';
    const INCLUDE_IF_NECESSARY = 'if-necessary';

    protected $_sortedSymbols;

    public function __construct($data = null) {
        parent::__construct($data);
        $this->setQuerySeparator('');
        $this->setParamSeparator('');
        $this->setFirstValueSeparator('');
        $this->setMultipleValueSeparator('');
    }

    public function getSortedSymbols() {
        if (!$this->_sortedSymbols) {
            $this->_sortedSymbols = $this->getJson('symbols');
            uasort($this->_sortedSymbols, array($this, '_compareSymbols'));
        }
        return $this->_sortedSymbols;
    }

    protected function _compareSymbols($a, $b) {
        /* @var $mbstring Mana_Core_Helper_Mbstring */
        $mbstring = Mage::helper('mana_core/mbstring');

        if ($mbstring->strpos($a['substitute'], $b['symbol']) !== false) {
            return 1;
        }
        if ($mbstring->strpos($b['substitute'], $a['symbol']) !== false) {
            return -1;
        }
        return 0;
    }
}