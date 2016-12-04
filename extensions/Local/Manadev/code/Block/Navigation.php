<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Block_Navigation extends Mage_Catalog_Block_Navigation
{
    const MANA_MENU_BLOCK_BEFORE_TEMPLATE = "mana_menu_%s_before";
    const MANA_MENU_BLOCK_AFTER_TEMPLATE = "mana_menu_%s_after";
    const MANA_MENU_BLOCK_SIBLING_AFTER_TEMPLATE = "mana_menu_%s_sibling_after";
    const MANA_MENU_BLOCK_SIBLING_BEFORE_TEMPLATE = "mana_menu_%s_sibling_before";

    protected function _construct() {
    }

    protected function _renderCategoryMenuItemHtml($category, $level = 0, $isLast = false, $isFirst = false,
        $isOutermost = false, $outermostItemClass = '', $childrenWrapClass = '', $noEventAttributes = false)
    {
        if (!$category->getIsActive()) {
            return '';
        }
        $html = array();

        // get all children
        // If Flat Data enabled then use it but only on frontend
        $flatHelper = Mage::helper('catalog/category_flat');
        if ($flatHelper->isAvailable() && $flatHelper->isBuilt(true) && !Mage::app()->getStore()->isAdmin()) {
            $children = (array)$category->getChildrenNodes();
            $childrenCount = count($children);
        } else {
            $children = $category->getChildren();
            $childrenCount = $children->count();
        }
        $before = $this->getStaticBlockHtml(self::MANA_MENU_BLOCK_BEFORE_TEMPLATE, $category);
        $after = $this->getStaticBlockHtml(self::MANA_MENU_BLOCK_AFTER_TEMPLATE, $category);
        $hasBeforeOrAfterBlock = strlen($before) || strlen($after);
        $hasChildren = ($children && $childrenCount) || $hasBeforeOrAfterBlock;

        // select active children
        $activeChildren = array();
        foreach ($children as $child) {
            if ($child->getIsActive()) {
                $activeChildren[] = $child;
            }
        }
        $activeChildrenCount = count($activeChildren);
        $hasActiveChildren = ($activeChildrenCount > 0) || $hasBeforeOrAfterBlock;

        // prepare list item html classes
        $classes = array();
        $classes[] = 'level' . $level;
        $classes[] = 'nav-' . $this->_getItemPosition($level);
        if ($this->isCategoryActive($category)) {
            $classes[] = 'active';
        }
        $linkClass = '';
        if ($isOutermost && $outermostItemClass) {
            $classes[] = $outermostItemClass;
            $linkClass = ' class="'.$outermostItemClass.'"';
        }
        if ($isFirst) {
            $classes[] = 'first';
        }
        if ($isLast) {
            $classes[] = 'last';
        }
        if ($hasActiveChildren) {
            $classes[] = 'parent';
        }

        // prepare list item attributes
        $attributes = array();
        if (count($classes) > 0) {
            $attributes['class'] = implode(' ', $classes);
        }
        if ($hasActiveChildren && !$noEventAttributes) {
             $attributes['onmouseover'] = 'toggleMenu(this,1)';
             $attributes['onmouseout'] = 'toggleMenu(this,0)';
        }

        // assemble list item with attributes
        $htmlLi = '<li';
        foreach ($attributes as $attrName => $attrValue) {
            $htmlLi .= ' ' . $attrName . '="' . str_replace('"', '\"', $attrValue) . '"';
        }
        $htmlLi .= '>';
        $html[] = $htmlLi;

        $html[] = '<a href="'.$this->getCategoryUrl($category).'"'.$linkClass.'>';
        $html[] = '<span>' . $this->escapeHtml($category->getName()) . '</span>';
        $html[] = '</a>';

        // render children
        $htmlChildren = '';
        $j = 0;
        foreach ($activeChildren as $child) {
            $htmlChildren .= $this->_renderCategoryMenuItemHtml(
                $child,
                ($level + 1),
                ($j == $activeChildrenCount - 1),
                ($j == 0),
                false,
                $outermostItemClass,
                $childrenWrapClass,
                $noEventAttributes
            );
            $j++;
        }
        if (!empty($htmlChildren) || $hasBeforeOrAfterBlock) {
            if ($childrenWrapClass) {
                $html[] = '<div class="' . $childrenWrapClass . '">';
            }
            $html[] = '<ul class="level' . $level . '">';

            if($before) {
                $html[] = $before;
            }

            if($htmlChildren) {
                $html[] = $htmlChildren;
            }

            if ($after) {
                $html[] = $after;
            }

            $html[] = '</ul>';
            if ($childrenWrapClass) {
                $html[] = '</div>';
            }
        }

        $html[] = '</li>';

        $html = implode("\n", $html);
        return $html;
    }

    public function renderCategoriesMenuHtml($level = 0, $outermostItemClass = '', $childrenWrapClass = '')
    {
        $activeCategories = array();
        foreach ($this->getStoreCategories() as $child) {
            if ($child->getIsActive()) {
                $activeCategories[] = $child;
            }
        }
        $activeCategoriesCount = count($activeCategories);
        $hasActiveCategoriesCount = ($activeCategoriesCount > 0);

        if (!$hasActiveCategoriesCount) {
            return '';
        }

        $html = '';
        $j = 0;
        foreach ($activeCategories as $category) {
            $html .= $this->getStaticBlockHtml(self::MANA_MENU_BLOCK_SIBLING_BEFORE_TEMPLATE, $category);
            $html .= $this->_renderCategoryMenuItemHtml(
                $category,
                $level,
                ($j == $activeCategoriesCount - 1),
                ($j == 0),
                true,
                $outermostItemClass,
                $childrenWrapClass,
                true
            );
            $html .= $this->getStaticBlockHtml(self::MANA_MENU_BLOCK_SIBLING_AFTER_TEMPLATE, $category);
            $j++;
        }

        return $html;
    }

    /**
     * @param $template
     * @param Mage_Catalog_Model_Category|mixed $category
     *
     * @return mixed
     */
    public function getStaticBlockHtml($template, $category) {
        $blockId = $template;
        $blockHtml = "";

        $url_key = Mage::getResourceModel('catalog/category')->getAttributeRawValue($category->getId(), "url_key", Mage::app()->getStore()->getId());
        $ids = array($url_key, $category->getId());
        foreach($ids as $id) {
            if ($id) {
                $blockId = sprintf($template, $id);
            }

            $collection = Mage::getModel('cms/block')->getCollection()
                ->addFieldToFilter('identifier', array(array('like' => $blockId . '_w%'), array('eq' => $blockId)))
                ->addFieldToFilter('is_active', 1);
            $blockId = $collection->getFirstItem()->getIdentifier();
            $blockHtml = Mage::app()->getLayout()->createBlock('cms/block')->setBlockId($blockId)->toHtml();
            if(trim($blockHtml) != "") {
                break;
            }
        }

        return $blockHtml;
    }
}