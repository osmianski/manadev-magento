<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterTree
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_FilterTree_Model_Solr_Subtree extends ManaPro_FilterTree_Model_Solr_Category {
    public function getCategory() {
        if ($this->coreHelper()->getRoutePath() == 'catalog/category/view' &&
            $this->coreHelper()->isManadevSeoLayeredNavigationInstalled())
        {
            if (($schema = $this->seoHelper()->getActiveSchema(Mage::app()->getStore()->getId())) &&
                $schema->getRedirectToSubcategory())
            {
                return $this->treeHelper()->getFirstLevelCategory($this->getCurrentCategory());
            }
            else {
                return $this->getCurrentCategory();
            }
        }
        return $this->treeHelper()->getRootCategory();
    }

    protected function _initItems() {
        parent::_initItems();
        if ($this->getCategory() != $this->treeHelper()->getRootCategory()) {
            $this->getFilterOptions()
                ->setData('name', $this->getCategory()->getName())
                ->setData('does_name_contains_category_name', true);
        }

        return $this;
    }
}