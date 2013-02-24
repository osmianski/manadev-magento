<?php
/**
 * @category    Mana
 * @package     Mana_Theme
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * Generic helper functions for Mana_Theme module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class Mana_Theme_Helper_Data extends Mage_Core_Helper_Abstract {
    protected $_configs = array();
    protected function _getThemeSection() {
        $config = $this->getConfig()->getNode();
        if ($result = (string)$config->system->configuration->section) {
            return $result;
        }
        else {
            throw new Exception('Configuration section for current theme not defined');
        }
    }
    public function getPageConfig($key) {
        /* @var $layout Mage_Core_Model_Layout */
        $layout = Mage::getSingleton('core/layout');
        if ($block = $layout->getBlock('m_theme_options')) {
            $blockField = str_replace('/', '_', $key);
            if ($block->hasData($blockField)) {
                return $block->getData($blockField);
            }
        }
        return Mage::getStoreConfig("{$this->_getThemeSection()}/{$key}");
    }
    public function getConfig($themeName = '') {
        if (!isset($this->_configs[$themeName])) {
            $this->_configs[$themeName] = new Mage_Core_Model_Config_Base();
            $this->_configs[$themeName]->loadString('<config/>');

            $params = array('_type' => 'etc');
            if ($themeName) {
                $themeNameParts = array_reverse(explode('/', $themeName));
                if (isset($themeNameParts[0])) {
                    $params['_theme'] = $themeNameParts[0];
                }
                if (isset($themeNameParts[1])) {
                    $params['_package'] = $themeNameParts[1];
                }
                if (isset($themeNameParts[2])) {
                    $params['_area'] = $themeNameParts[2];
                }
                else {
                    $params['_area'] = 'frontend';
                }
            }
            $filename = Mage::getDesign()->getFilename('config.xml', $params);
            if (file_exists($filename)) {
                $fileConfig = new Mage_Core_Model_Config_Base();
                if ($fileConfig->loadFile($filename)) {
                    $this->_configs[$themeName]->extend($fileConfig, true);
                }
            }

            Mage::helper('mana_core')->translateConfig($this->_configs[$themeName]->getNode());
        }
        return $this->_configs[$themeName];
    }

    public function inCart($product) {
        /* @var $checkout Mage_Checkout_Helper_Data */
        $checkout = Mage::helper('checkout');

        return $checkout->getQuote()->getItemByProduct($product) ? true : false;
    }

    public function inWishlist($product)
    {
        /* @var $wishlist Mage_Wishlist_Helper_Data */
        $wishlist = Mage::helper('wishlist');

        foreach ($wishlist->getWishlist()->getItemCollection() as $item) {
            /* @var $item Mage_Wishlist_Model_Item */
            if ($item->getProductId() == $product->getId()) {
                return $item;
            }
        }
        return false;
    }

    public function inCompare($product) {
        /* @var $compare Mage_Catalog_Helper_Product_Compare */
        $compare = Mage::helper('catalog/product_compare');

        foreach ($compare->getItemCollection() as $item) {
            /* @var $item Mage_Catalog_Model_Product_Compare_Item */
            if ($item->getProductId() == $product->getId()) {
                return $item;
            }
        }
        return false;
    }

    public function getFullActionName($delimiter = '_') {
        $request = Mage::app()->getRequest();
        return $request->getRequestedRouteName() . $delimiter .
                $request->getRequestedControllerName() . $delimiter .
                $request->getRequestedActionName();

    }
    public function isHomePage() {
        return $this->getFullActionName() =='cms_index_index';
    }
    /**
     * @param mage_Core_Block_Abstract $block
     */
    public function getCssClass($block, $type) {
        return $block->getCssClass() ? $block->getCssClass() :
            (strpos($type, '/') !== false ? $this->getPageConfig($type) : $this->getPageConfig('css/'.$type));
    }

    public function isVisibleInProductList($block, $part) {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper(strtolower('Mana_Core'));

        if ($block instanceof ManaPage_Bestseller_Block_Widget) {
            $mode = $core->endsWith($block->getTemplate(), 'list.phtml') ? 'bestseller_list' : 'bestseller_grid';
        }
        elseif ($block instanceof Mage_Catalog_Block_Product_New) {
            $mode = $core->endsWith($block->getTemplate(), 'new_list.phtml') ? 'new_list' : 'new_grid';
        }
        elseif ($block instanceof Mage_Reports_Block_Product_Viewed) {
            $mode = $core->endsWith($block->getTemplate(), 'viewed_list.phtml') ? 'viewed_list' : 'viewed_grid';
        }
        elseif ($block instanceof Mage_Reports_Block_Product_Compared) {
            $mode = $core->endsWith($block->getTemplate(), 'compared_list.phtml') ? 'compared_list' : 'compared_grid';
        }
        elseif ($block instanceof Mage_Catalog_Block_Product_View) {
            $mode = 'product';
        }
        else {
            $mode = $block->getMode();
        }
        $configKey = "show_in_{$mode}/{$part}";
        $key = "m_visible_{$part}_in_{$mode}";

        if ($block->hasData($key)) {
            return $block->getData($key);
        }
        else {
            return $this->getPageConfig($configKey);
        }
    }

    public function getListItemClass($block, $product) {
        $buttonCount = 0;
        $iconCount = 0;
        $linkCount = 0;

        $cartClass = $this->inCart($product) ? 'in-cart' : '';
        $wishlistClass = $this->inWishlist($product) ? 'in-wishlist' : '';
        $compareClass = $this->inCompare($product) ? 'in-compare' : '';

        if ($product->isSaleable()) {
            switch ($this->isVisibleInProductList($block, 'cart')) {
                case Mana_Theme_Model_Source_Buttoniconlink::BUTTON: $cartClass .= ' cart-button'; $buttonCount++; break;
                case Mana_Theme_Model_Source_Buttoniconlink::ICON: $cartClass .= ' cart-icon'; $iconCount++; break;
                case Mana_Theme_Model_Source_Buttoniconlink::LINK: $cartClass .= ' cart-link'; $linkCount++; break;
            }
        }
        else {
            if ($this->isVisibleInProductList($block, 'out_of_stock')) {
                switch ($this->isVisibleInProductList($block, 'cart')) {
                    case Mana_Theme_Model_Source_Buttoniconlink::BUTTON: $buttonCount++; break;
                    case Mana_Theme_Model_Source_Buttoniconlink::LINK: $iconCount++; break;
                    case Mana_Theme_Model_Source_Buttoniconlink::LINK: $linkCount++; break;
                }
            }
        }
        if (Mage::helper('wishlist')->isAllow()) {
            switch ($this->isVisibleInProductList($block, 'wishlist')) {
                case Mana_Theme_Model_Source_Buttoniconlink::BUTTON: $wishlistClass .= ' wishlist-button'; $buttonCount++; break;
                case Mana_Theme_Model_Source_Buttoniconlink::ICON: $wishlistClass .= ' wishlist-icon'; $iconCount++; break;
                case Mana_Theme_Model_Source_Buttoniconlink::LINK: $wishlistClass .= ' wishlist-link'; $linkCount++; break;
            }
        }
        if ($block->getAddToCompareUrl($product)) {
            switch ($this->isVisibleInProductList($block, 'compare')) {
                case Mana_Theme_Model_Source_Buttoniconlink::BUTTON: $compareClass .= ' compare-button'; $buttonCount++; break;
                case Mana_Theme_Model_Source_Buttoniconlink::ICON: $compareClass .= ' compare-icon'; $iconCount++; break;
                case Mana_Theme_Model_Source_Buttoniconlink::LINK: $compareClass .= ' compare-link'; $linkCount++; break;
            }
        }

        return "with-{$buttonCount}-btn with-{$iconCount}-icons with-{$linkCount}-links {$cartClass} {$wishlistClass} {$compareClass}";
    }

    public function getHeight($width) {
        $ratio = explode(':', $this->getPageConfig("general/image_ratio"));
        if (count($ratio) != 2) {
            $ratio = array(1, 1);
        }
        $nom = trim($ratio[0]);
        $denom = trim($ratio[1]);
        if (!$nom || !$denom || !is_numeric($nom) || !is_numeric($denom)) {
            $nom = 1;
            $denom = 1;
        }
        return round(($width / $nom) * $denom);
   }
}