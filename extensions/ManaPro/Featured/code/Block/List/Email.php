<?php
/**
 * @category    Mana
 * @package     ManaPro_Featured
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_Featured_Block_List_Email extends ManaPro_Featured_Block_List {
    public function getConfigSource() {
        return 'mana_featured/category';
    }
    protected function _beforeToHtml() {
        $this->_prepareCollection()->addCategoryFilter()->addFeaturedFilter();
        $this->getCollection()->load();
        Mage::getModel('review/review')->appendSummary($this->getCollection());
        return parent::_beforeToHtml();
    }
}