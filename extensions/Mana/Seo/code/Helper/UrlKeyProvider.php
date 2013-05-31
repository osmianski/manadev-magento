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
abstract class Mana_Seo_Helper_UrlKeyProvider extends Mage_Core_Helper_Abstract {
    /**
     * @param string[] $candidates
     * @param int $storeId
     * @param bool $isPage
     * @param bool $isParameter
     * @param bool $isFirstValue
     * @param bool $isMultipleValue
     * @param Mana_Seo_Model_Url[] $activeUrlKeys
     * @param Mana_Seo_Model_Url[] $obsoleteUrlKeys
     */
    abstract public function getUrlKeys($candidates, $storeId, $isPage, $isParameter, $isFirstValue,
            $isMultipleValue, &$activeUrlKeys, &$obsoleteUrlKeys);
}