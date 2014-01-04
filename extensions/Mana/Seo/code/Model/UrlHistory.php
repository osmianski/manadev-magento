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
 * @method string getType()
 * @method Mana_Seo_Model_UrlHistory setType(string $value)
 */
class Mana_Seo_Model_UrlHistory extends Mana_Db_Model_Entity {
    const TYPE_CATEGORY_SUFFIX =       'category_suffix';
    const TYPE_HOME_PAGE_SUFFIX =      'home_page_suffix';
    const TYPE_CMS_PAGE_SUFFIX =       'cms_page_suffix';
    const TYPE_SEARCH_SUFFIX =         'search_suffix';
    const TYPE_ATTRIBUTE_PAGE_SUFFIX = 'attr_page_suffix';
    const TYPE_OPTION_PAGE_SUFFIX =    'opt_page_suffix';
}