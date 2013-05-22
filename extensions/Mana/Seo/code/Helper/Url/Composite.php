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
class Mana_Seo_Helper_Url_Composite extends Mana_Seo_Helper_Url {
    /**
     * @return bool|Mana_Seo_Helper_Url_Composite_Page
     */
    public function getPageHelper() {
        /** @noinspection PhpUndefinedFieldInspection */
        if (($xml = $this->getXml()) && ($helper = (string)$xml->page_helper)) {
            return Mage::helper($helper);
        }
        else {
            return false;
        }
    }

    /**
     * @return bool|Mana_Seo_Helper_Url_Composite_Parameter
     */
    public function getParameterHelper() {
        /** @noinspection PhpUndefinedFieldInspection */
        if (($xml = $this->getXml()) && ($helper = (string)$xml->parameter_helper)) {
            return Mage::helper($helper);
        }
        else {
            return false;
        }
    }

    public function isPage() {
        return ($helper = $this->getPageHelper()) && $helper->isPage();
    }

    public function isParameter() {
        return ($helper = $this->getParameterHelper()) && $helper->isParameter();
    }

    public function isValue() {
        return ($helper = $this->getParameterHelper()) && $helper->isValue();
    }
}