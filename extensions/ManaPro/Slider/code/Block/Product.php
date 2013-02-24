<?php
/**
 * @category    Mana
 * @package     ManaPro_Slider
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 * @method ManaPro_Slider_Model_Product getProduct()
 * @method ManaPro_Slider_Block_Product setProduct(ManaPro_Slider_Model_Product $value)
 * @method Mage_Core_Model_Config_Element getDisplayOptions()
 * @method ManaPro_Slider_Block_Product setDisplayOptions(Mage_Core_Model_Config_Element $value)
 */
class ManaPro_Slider_Block_Product extends Mage_Catalog_Block_Product_Abstract {
    public function init() {
        $this->setTemplate((string)$this->getDisplayOptions()->template);
        return $this;
    }
    public function getPosition() {
        return $this->getProduct()->getPosition();
    }

    public function getChildName() {
        return $this->getProduct()->getBlockLocalName();
    }

    public function getCatalogProduct() {
        return $this->getProduct()->getProduct();
    }

    public function isVisible() {
        return $this->getCatalogProduct() ? true : false;
    }

    public function _getImageInfo() {
        $product = $this->getCatalogProduct();
        if ($imageIndex = $this->getProduct()->getImageIndex()) {
            if (!($product->hasData('media_gallery'))) {
                $product->load($product->getId());
                //$product->getResource()->walkAttributes('backend/afterLoad', array($product));
            }
            if ($images = $this->_getMediaGalleryImages()) {
                $foundImage = '';
                foreach ($images as $image) {
                    if ($image['position'] == $imageIndex) {
                        $foundImage = $image;
                        break;
                    }
                }
                if ($foundImage) {
                    return $foundImage;
                }
            }
        }
        return null;
    }

    protected function _getMediaGalleryImages()
    {
        $product = $this->getCatalogProduct();
        if(!$this->hasData('media_gallery_images') && is_array($product->getMediaGallery('images'))) {
            $images = new Varien_Data_Collection();
            foreach ($product->getMediaGallery('images') as $image) {
                $image['url'] = $product->getMediaConfig()->getMediaUrl($image['file']);
                $image['id'] = isset($image['value_id']) ? $image['value_id'] : null;
                $image['path'] = $product->getMediaConfig()->getMediaPath($image['file']);
                $images->addItem(new Varien_Object($image));
            }
            $this->setData('media_gallery_images', $images);
        }

        return $this->getData('media_gallery_images');
    }

    public function getImage()
    {
        $product = $this->getCatalogProduct();
        if ($info = $this->_getImageInfo()) {
            return $this->helper('catalog/image')->init($product, 'slider_image', $info['file']);
        }
        else {
            return $this->helper('catalog/image')->init($product, 'small_image');
        }
    }

    public function getImageTitle() {
        $product = $this->getCatalogProduct();
        if ($info = $this->_getImageInfo()) {
            return $info['label'];
        } else {
            return $this->getImageLabel($product, 'small_image');
        }
    }
}