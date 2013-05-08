<?php
/** 
 * @category    Mana
 * @package     Mana_Seo
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 * @method string getUrlKey()
 * @method Mana_Seo_Model_UrlHistory setUrlKey(string $value)
 * @method string getRedirectTo()
 * @method Mana_Seo_Model_UrlHistory setRedirectTo(string $value)
 */
class Mana_Seo_Model_UrlHistory extends Mana_Db_Model_Entity {
    const TYPE_CATEGORY_SUFFIX =     'category_suffix';
    const TYPE_CMS_PAGE_IDENTIFIER = 'cms_identifier';
    const TYPE_OPTION_PAGE_SUFFIX =  'option_page_suffix';
    const TYPE_OPTION_PAGE_URL_KEY = 'option_page_url_key';
    const TYPE_ATTRIBUTE_PAGE_SUFFIX = 'attr_page_suffix';
    const TYPE_ATTRIBUTE_PAGE_URL_KEY = 'attr_page_url_key';
}