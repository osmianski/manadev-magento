<?php
/** 
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Core_Rewrite_PageCache_Processor extends Enterprise_PageCache_Model_Processor {
    protected function _getFullPageUrl() {
        if ($url = Mage::registry('m_original_request_uri')) {
            $uri = '';
            if (isset($_SERVER['HTTP_HOST'])) {
                $uri = $_SERVER['HTTP_HOST'];
            } elseif (isset($_SERVER['SERVER_NAME'])) {
                $uri = $_SERVER['SERVER_NAME'];
            }
            if ($uri) {
                return $uri . $url;
            }
        }
        return parent::_getFullPageUrl();
    }
}