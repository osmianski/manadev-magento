<?php
/**
 * @category    Mana
 * @package     ManaPro_ProductPlusProduct
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Enter description here ...
 * @author Mana Team
 *
 */
class ManaPro_ProductPlusProduct_Bought_TogetherController extends Mana_ProductLists_Controller_Admin {
    protected function _construct()
    {
        $this->setUsedModuleName('ManaPro_ProductPlusProduct');
        $this->_linkType = ManaPro_ProductPlusProduct_Resource_Setup::LINK_TYPE;
    }
}