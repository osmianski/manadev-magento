<?php
/** 
 * @category    Mana
 * @package     ManaSlider_Tabbed
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 * @method ManaSlider_Tabbed_Model_Tab[] getTabs()
 * @method int getHeight()
 */
class ManaSlider_Tabbed_Block_ProductSlider extends Mage_Catalog_Block_Product_Abstract
{
    protected function _construct() {
        parent::_construct();
        $this->setTemplate('manaslider/tabbed/product-slider.phtml');
    }

    /**
     * @param SimpleXmlElement $xml
     * @param bool $ajax
     */
    public function prepare($xml, $ajax) {
        $this->setData('xml', $xml->asXML());
        $data = array();
        foreach ($xml->children() as $tabPropertyXml) {
            /* @var $tabPropertyXml SimpleXmlElement */
            if ($tabPropertyXml->getName() == 'max_product_count') {
                $data['starting_from_product'] = $ajax ? (string)$tabPropertyXml : 0;
                $data['max_product_count'] = $ajax ? null : (string)$tabPropertyXml;
            }
            else {
                $data[$tabPropertyXml->getName()] = (string)$tabPropertyXml;
            }

        }
        $this->addData($data);

        if (($dataSource = $data['data_source']) &&
            ($dataSourceXml = Mage::getConfig()->getNode("manaslider_tabbed/data_sources/$dataSource")))
        {
            unset($data['data_source']);

            list($sourceHelper, $method) = explode('::', (string)$dataSourceXml->source_helper);
            $sourceHelper = Mage::helper($sourceHelper);
            $this->setData('data_source', $sourceHelper->$method($this));


            $block = $this->getLayout()->createBlock('manaslider_tabbed/productList',
                $this->getNameInLayout().'.product_list');
            $this->append($block, 'product_list');

            $block->setData('data_source', $this->getData('data_source'));
            $block->setData('image_width', $this->getData('image_width'));
            $block->setData('image_height', $this->getData('image_height'));
            $block->setData('is_visible_name', $this->getData('is_visible_name'));
            $block->setData('is_visible_rating', $this->getData('is_visible_rating'));
            $block->setData('is_visible_price', $this->getData('is_visible_price'));
            $block->setData('is_visible_description', $this->getData('is_visible_description'));
            $block->setData('is_visible_read_more', $this->getData('is_visible_read_more'));
            $block->setData('is_visible_add_to_cart', $this->getData('is_visible_add_to_cart'));
            $block->setData('is_visible_add_to_wishlist', $this->getData('is_visible_add_to_wishlist'));
            $block->setData('is_visible_add_to_compare', $this->getData('is_visible_add_to_compare'));

            $block->setData('starting_from_product', $this->getData('starting_from_product'));
            $block->setData('rotation_duration', $this->getData('rotation_duration'));
            $block->setData('slide_count', $this->getData('slide_count'));


        }
    }

    #region Dependencies
    /**
     * @return Mana_Core_Helper_Layout
     */
    public function layoutHelper() {
        return Mage::helper('mana_core/layout');
    }

    /**
     * @return Mage_Core_Model_Layout
     */
    public function getLayout() {
        return Mage::getSingleton('core/layout');
    }

    /**
     * @return Mana_Core_Helper_Js
     */
    public function jsHelper() {
        return Mage::helper('mana_core/js');
    }

    /**
     * @return Mana_Core_Helper_Json
     */
    public function jsonHelper() {
        return Mage::helper('mana_core/json');
    }

    /**
     * @return Mana_Core_Helper_UrlTemplate
     */
    public function urlTemplateHelper() {
        return Mage::helper('mana_core/urlTemplate');
    }
    #endregion
}