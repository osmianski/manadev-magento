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
abstract class Mana_Seo_Helper_VariationPoint_Suffix extends Mana_Seo_Helper_VariationPoint {
    protected $_applicableSuffixes = array();
    protected $_historyType;

    /**
     * @param Mana_Seo_Model_Context $context
     * @return Mana_Seo_Helper_VariationPoint_Suffix
     */
    protected function _before(/** @noinspection PhpUnusedParameterInspection */$context) {
        return $this;
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @param string $suffix
     * @return bool
     */
    protected function _register($context, $suffix) {
        /* @var $logger Mana_Core_Helper_Logger */
        $logger = Mage::helper('mana_core/logger');

        $logger->beginSeo("Checking suffix '$suffix' ...");
        /* @var $mbstring Mana_Core_Helper_Mbstring */
        $mbstring = Mage::helper('mana_core/mbstring');

        $context->setSuffix($suffix);
        $path = $context->getPath();
        if ($suffix) {
            $path = $mbstring->substr($path, 0, $mbstring->strlen($path) - $mbstring->strlen($suffix));
        }
        $context->pushData('path', $path);

        return true;
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @param string $suffix
     * @return Mana_Seo_Helper_VariationPoint_Suffix
     */
    protected function _unregister(/** @noinspection PhpUnusedParameterInspection */ $context, $suffix) {
        /* @var $logger Mana_Core_Helper_Logger */
        $logger = Mage::helper('mana_core/logger');

        $context->unsetData('suffix');
        $context->popData('path');

        $logger->endSeo();

        return $this;
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @return Mana_Seo_Helper_VariationPoint_Suffix
     */
    protected function _after(/** @noinspection PhpUnusedParameterInspection */$context) {
        return $this;
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @param string[] $activeSuffixes
     * @param string[] $obsoleteSuffixes
     * @return Mana_Seo_Helper_VariationPoint_Suffix
     */
    protected function _getSuffixes($context, &$activeSuffixes, &$obsoleteSuffixes) {
        $activeSuffixes = array();
        $obsoleteSuffixes = array();

        $currentSuffix = $this->getCurrentSuffix();
        foreach ($this->_getApplicableSuffixes($context, $currentSuffix, $this->_getHistoryType()) as $suffix => $redirect) {
            if (!$redirect) {
                $activeSuffixes[] = $suffix;
            }
            else {
                $obsoleteSuffixes[] = $suffix;
            }
        }
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @return bool
     */
    public function match($context) {
        /* @var $seo Mana_Seo_Helper_Data */
        $seo = Mage::helper('mana_seo');

        $allObsoleteSuffixes = array();
        $action = $context->getAction();

        $this->_before($context);
        $this->_getSuffixes($context, $activeSuffixes, $obsoleteSuffixes);
        foreach ($activeSuffixes as $suffix) {
            if ($this->_matchDeeper($context, $suffix, $seo)) {
                return true;
            }
        }
        $allObsoleteSuffixes = array_merge($allObsoleteSuffixes, $obsoleteSuffixes);

        $context->setAction(Mana_Seo_Model_Context::ACTION_REDIRECT);
        foreach ($allObsoleteSuffixes as $suffix) {
            if ($this->_matchDeeper($context, $suffix, $seo)) {
                return true;
            }
        }

        $context->setAction($action);
        $this->_after($context);

        return false;
    }


    /**
     * @param Mana_Seo_Model_Context $context
     * @param string $currentSuffix
     * @param string $urlHistoryType
     * @return bool[] array
     */
    protected function _getApplicableSuffixes($context, $currentSuffix, $urlHistoryType) {
        if (!isset($this->_applicableSuffixes[$urlHistoryType])) {
            /* @var $dbHelper Mana_Db_Helper_Data */
            $dbHelper = Mage::helper('mana_db');

            $suffixes = array();
            if ($this->_matchSuffix($context, $currentSuffix)) {
                $suffixes[$currentSuffix] = false;
            }
            /* @var $oldSuffixCollection Mana_Db_Resource_Entity_Collection */
            $oldSuffixCollection = $dbHelper->getResourceModel('mana_seo/urlHistory_collection');
            $oldSuffixCollection->getSelect()
                ->where('type = ?', $urlHistoryType)
                ->where('store_id IN(?)', array(0, $context->getStoreId()))
                ->where('url_key <> ?', $currentSuffix);
            foreach ($oldSuffixCollection as $historyRecord) {
                /* @var $historyRecord Mana_Seo_Model_UrlHistory */
                $suffix = $historyRecord->getUrlKey();
                if (!isset($suffixes[$suffix]) && $this->_matchSuffix($context, $suffix)) {
                    $suffixes[$suffix] = true;
                }

            }

            $this->_applicableSuffixes[$urlHistoryType] = $suffixes;
        }

        return $this->_applicableSuffixes[$urlHistoryType];

    }

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

    abstract public function getCurrentSuffix();
    protected function _getHistoryType() {
        return $this->_historyType;
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @param string $suffix
     * @param Mana_Seo_Helper_Data $seo
     * @return bool
     */
    protected function _matchDeeper($context, $suffix, $seo) {
        if ($this->_register($context, $suffix)) {
            if ($seo->getParameterVariationPoint()->match($context)) {
                return true;
            }
            $this->_unregister($context, $suffix);
        }
        return false;
    }
}