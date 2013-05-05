<?php
/** 
 * @category    Mana
 * @package     Mana_Seo
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
abstract class Mana_Seo_Helper_PageType extends Mana_Seo_Helper_VariationSource {
    /**
     * @param Mana_Seo_Model_Context $context
     * @param string $suffix
     * @return bool
     */
    protected function _matchSuffix($context, $suffix) {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper('mana_core');

        if ($suffix) {
            if ($suffix == '/') {
                return $context->getOriginalSlash() == '/';
            }
            else {
                return $core->endsWith($context->getPath(), $suffix);
            }
        }
        else {
            return true;
        }
    }

    protected function _removeSuffix($haystack, $suffix) {
        /* @var $mbstring Mana_Core_Helper_Mbstring */
        $mbstring = Mage::helper('mana_core/mbstring');

        if ($suffix && $suffix != '/') {
            return $mbstring->substr($haystack, 0,
                $mbstring->strlen($haystack) - $mbstring->strlen($suffix));
        }
        else {
            return $haystack;
        }
    }

    protected function _addSuffix($haystack, $suffix) {
        if ($suffix && $suffix != '/') {
            return $haystack . $suffix;
        }
        else {
            return $haystack;
        }
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @param string $currentSuffix
     * @param string $urlHistoryType
     * @return bool[] array
     */
    protected function _getApplicableSuffixes($context, $currentSuffix, $urlHistoryType) {
        /* @var $dbHelper Mana_Db_Helper_Data */
        $dbHelper = Mage::helper('mana_db');

        $suffixes = array();
        if ($this->_matchSuffix($context, $currentSuffix)) {
            $suffixes[$currentSuffix] = false;
        }
        /* @var $oldSuffixCollection Mana_Db_Resource_Entity_Collection */
        $oldSuffixCollection = $dbHelper->getResourceModel('mana_seo/urlHistory_collection');
        $oldSuffixCollection->getSelect()
            ->where('page_type = ?', $urlHistoryType)
            ->where('store_id IN(?)', array(0, $context->getStoreId()))
            ->where('old_url <> ?', $currentSuffix);
        foreach ($oldSuffixCollection as $historyRecord) {
            /* @var $historyRecord Mana_Seo_Model_UrlHistory */
            $suffix = $historyRecord->getOldUrl();
            if (!isset($suffixes[$suffix]) && $this->_matchSuffix($context, $suffix)) {
                $suffixes[$suffix] = true;
            }

        }
        return $suffixes;
    }
}