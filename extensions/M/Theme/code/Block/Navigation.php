<?php
/**
 * @category    Mana
 * @package     M_Theme
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class M_Theme_Block_Navigation extends Mage_Catalog_Block_Navigation {
    protected function _construct() {
        parent::_construct();
        $this->setCacheLifetime(null);
    }
}