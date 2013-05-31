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
class Mana_Seo_Helper_Url_Value extends Mana_Seo_Helper_Url {
    /**
     * @param Mana_Seo_Model_ParsedUrl $parsedUrl
     * @param Mana_Seo_Model_Url $urlKey
     * @return bool
     */
    public function registerValue($parsedUrl, $urlKey) {
        $parsedUrl->addParameter($urlKey->getOptionAttributeCode(), $urlKey->getOptionId());

        return true;
    }

}