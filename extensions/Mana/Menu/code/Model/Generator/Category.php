<?php
/** 
 * @category    Mana
 * @package     Mana_Menu
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Menu_Model_Generator_Category extends Mana_Menu_Model_Generator {
    /**
     * @param Mage_Core_Model_Config_Element $element
     */
    public function extend($element) {
        $this->_extendRecursively($element, Mage::helper('catalog/category')->getStoreCategories());
    }

    protected function _extendRecursively($element, $categories) {
        foreach ($categories as $id => $category) {
            /* @var $category Mage_Catalog_Model_Category */
            $xmlId = 'c-' . $id;
            $element->items->$xmlId->url = $category->getUrl();
            $element->items->$xmlId->route = "catalog/category/view/id/$id";
            $element->items->$xmlId->label = $category->getName();
            if ($childCategories = $category->getData('children_nodes')) {
                $this->_extendRecursively($element->items->$xmlId, $childCategories);
            }
        }
    }
}