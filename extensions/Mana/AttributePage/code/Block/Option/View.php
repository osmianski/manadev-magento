<?php
/** 
 * @category    Mana
 * @package     Mana_AttributePage
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_AttributePage_Block_Option_View extends Mage_Core_Block_Template {
    /**
     * @return Mana_AttributePage_Model_Option_Page
     */
    public function getOptionPage() {
        throw new Exception('Not implemented');
    }

    public function getProductListHtml() {
        return $this->getChildHtml('product_list');
    }

}