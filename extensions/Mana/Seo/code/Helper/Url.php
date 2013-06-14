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
class Mana_Seo_Helper_Url extends Mage_Core_Helper_Abstract {
    /**
     * @param Mana_Seo_Model_ParsedUrl $parsedUrl
     * @param Mana_Seo_Model_Url $urlKey
     * @throws Exception
     * @return bool
     */
    public function registerPage(/** @noinspection PhpUnusedParameterInspection */ $parsedUrl, $urlKey) {
        throw new Exception('Not implemented');
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl $parsedUrl
     * @param Mana_Seo_Model_Url $urlKey
     * @throws Exception
     * @return bool
     */
    public function registerParameter(/** @noinspection PhpUnusedParameterInspection */ $parsedUrl, $urlKey) {
        throw new Exception('Not implemented');
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl $parsedUrl
     * @param Mana_Seo_Model_Url $urlKey
     * @throws Exception
     * @return bool
     */
    public function registerValue(/** @noinspection PhpUnusedParameterInspection */ $parsedUrl, $urlKey) {
        throw new Exception('Not implemented');
    }

    /**
     * @throws Exception
     * @return string
     */
    protected function _getSuffix() {
        throw new Exception('Not implemented');
    }

    /**
     * @throws Exception
     * @return string
     */
    protected function _getSuffixHistoryType() {
        throw new Exception('Not implemented');
    }

    /**
     * @param string $path
     * @param string $originalSlash
     * @param int $storeId
     * @param string[] $activeSuffixes
     * @param string[] $obsoleteSuffixes
     */
    public function getSuffixes($path, $originalSlash, $storeId, &$activeSuffixes, &$obsoleteSuffixes) {
        $activeSuffixes = array();
        $obsoleteSuffixes = array();

        if (!($urlHistoryType = $this->_getSuffixHistoryType())) {
            return;
        }
        $currentSuffix = (string)$this->_getSuffix();

        /* @var $dbHelper Mana_Db_Helper_Data */
        $dbHelper = Mage::helper('mana_db');

        $suffixes = array();
        if ($this->_matchSuffix($path, $originalSlash, $currentSuffix)) {
            $activeSuffixes[] = $currentSuffix;
        }
        /* @var $oldSuffixCollection Mana_Db_Resource_Entity_Collection */
        $oldSuffixCollection = $dbHelper->getResourceModel('mana_seo/urlHistory_collection');
        $oldSuffixCollection->getSelect()
            ->where('type = ?', $urlHistoryType)
            ->where('store_id IN(?)', array(0, $storeId))
            ->where('url_key <> ?', $currentSuffix);
        foreach ($oldSuffixCollection as $historyRecord) {
            /* @var $historyRecord Mana_Seo_Model_UrlHistory */
            $suffix = (string)$historyRecord->getUrlKey();
            if (!in_array($suffix, $obsoleteSuffixes) && $this->_matchSuffix($path, $originalSlash, $suffix)) {
                $obsoleteSuffixes[] = $suffix;
            }

        }
    }

    /**
     * @param string $path
     * @param string $originalSlash
     * @param string $suffix
     * @return bool
     */
    protected function _matchSuffix($path, $originalSlash, $suffix) {
        if ($suffix) {
            if ($suffix == '/') {
                return $originalSlash == '/';
            }
            else {
                /* @var $mbstring Mana_Core_Helper_Mbstring */
                $mbstring = Mage::helper('mana_core/mbstring');

                return $mbstring->endsWith($path, $suffix);
            }
        }
        else {
            return true;
        }
    }

}