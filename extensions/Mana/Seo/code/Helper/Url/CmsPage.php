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
class Mana_Seo_Helper_Url_CmsPage extends Mana_Seo_Helper_Url {
    protected $_type = 'cms_page';

    public function isPage() {
        return true;
    }

}