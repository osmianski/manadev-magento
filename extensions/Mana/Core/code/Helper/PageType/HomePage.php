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
class Mana_Core_Helper_PageType_HomePage extends Mana_Core_Helper_PageType_CmsPage  {
    public function getRoutePath() {
        return 'cms/index/index';
    }

    /**
     * @return bool|string
     */
    public function getConditionLabel() {
        return $this->__('CMS Home Page');
    }
}