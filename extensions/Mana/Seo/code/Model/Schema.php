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
 * @method Mana_Seo_Model_Schema overrideQuerySeparator(string $value)
 * @method string getParamSeparator() '/' in 'url/p1-v1_v2/p2-v3_v4_v5.html'
 * @method Mana_Seo_Model_Schema overrideParamSeparator(string $value)
 * @method string getFirstValueSeparator() '-' in 'url/p1-v1_v2/p2-v3_v4_v5.html'
 * @method Mana_Seo_Model_Schema overrideFirstValueSeparator(string $value)
 * @method string getMultipleValueSeparator() '_' in 'url/p1-v1_v2/p2-v3_v4_v5.html'
 * @method Mana_Seo_Model_Schema overrideMultipleValueSeparator(string $value)
 * @method string getPriceSeparator()
 * @method Mana_Seo_Model_Schema overridePriceSeparator(string $value)
 * @method string getStatus()
 * @method Mana_Seo_Model_Schema overrideStatus(string $value)
 * @method int getUseFilterLabels()
 * @method Mana_Seo_Model_Schema overrideUseFilterLabels(int $value)
 * @method string getSymbols()
 * @method Mana_Seo_Model_Schema overrideSymbols(string $value)
 * @method string getToolbarUrlKeys()
 * @method Mana_Seo_Model_Schema overrideToolbarUrlKeys(string $value)
 * @method string getName()
 * @method Mana_Seo_Model_Schema overrideName(string $value)
 * @method string getInternalName()
 * @method Mana_Seo_Model_Schema overrideInternalName(string $value)
 * @method bool getRedirectParameterOrder()
 * @method Mana_Seo_Model_Schema overrideRedirectParameterOrder(bool $value)
 * @method string getIncludeFilterName()
 * @method Mana_Seo_Model_Schema overrideIncludeFilterName(string $value)
 * @method bool getUseRangeBounds()
 * @method Mana_Seo_Model_Schema overrideUseRangeBounds(bool $value)
 * @method bool getRedirectToSubcategory()
 * @method Mana_Seo_Model_Schema overrideRedirectToSubcategory(bool $value)
 * @method bool getRedirectToOptionPage()
 * @method Mana_Seo_Model_Schema overrideRedirectToOptionPage(bool $value)
 * @method string getUpdatedAt()
 * @method Mana_Seo_Model_Schema overrideUpdatedAt(string $value)
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

    public function affectsUrl($key) {
        return ($field = $this->dbConfigHelper()->getScopeField($this->_scope, $key)) && isset($field->affects_url);
    }

    public function getFieldsAffectingUrl() {
        $result = array();
        foreach ($this->getFieldsXml() as $fieldXml) {
            if (isset($fieldXml->affects_url)) {
                $result[] = (string)$fieldXml->name;
            }
        }

        return $result;
    }

    protected function _beforeSave() {
        $this->overrideUpdatedAt(now());
        return parent::_beforeSave();
    }
    public function afterCommitCallback() {
        if (!Mage::registry('m_prevent_indexing_on_save')) {
            /* @var $indexer Mage_Index_Model_Indexer */
            $indexer = Mage::getSingleton('index/indexer');

            $indexer->getProcessByCode('mana_seo')
                ->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX)
                ->reindexAll();
        }
    }
}