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
class Mana_Seo_Helper_Url_Filter extends Mana_Seo_Helper_Url_Composite {
    protected $_type = 'filter';

    public function isManadevLayeredNavigationInstalled() {
        return $this->getParameterHelper() != null;
    }
}