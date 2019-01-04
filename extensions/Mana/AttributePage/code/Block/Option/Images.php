<?php
/** 
 * @category    Mana
 * @package     Mana_AttributePage
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 *
 * @method string getImageToShow()
 */
abstract class Mana_AttributePage_Block_Option_Images extends Mage_Core_Block_Template {
    const PRODUCT_IMAGE = 'product';
    const FEATURED_IMAGE = 'featured';
    const BASE_IMAGE = 'base';

    /**
     * @return Mana_AttributePage_Resource_OptionPage_Store_Collection
     */
    abstract public function getCollection();

    public function getImageUrlColumn() {
        return $this->getImageToShow() == self::BASE_IMAGE ? 'image' : "{$this->getImageToShow()}_image";
    }

    public function getImageWidthColumn() {
        return $this->getImageToShow() == self::BASE_IMAGE ? 'image_width' : "{$this->getImageToShow()}_image_width";
    }
    public function getImageHeightColumn() {
        return $this->getImageToShow() == self::BASE_IMAGE ? 'image_height' : "{$this->getImageToShow()}_image_height";
    }

    /**
     * @param Mana_AttributePage_Model_OptionPage_Store $optionPage
     * @return string|null
     */
    public function getImageUrl($optionPage) {
        $result = $optionPage->getData($this->getImageUrlColumn());
        if (!$result) {
            return null;
        }

        if (!$this->filesHelper()->shouldRenderImage($result)) {
            return null;
        }

        return $result;
    }

    /**
     * @param Mana_AttributePage_Model_OptionPage_Store $optionPage
     * @return string|null
     */
    public function getImageWidth($optionPage) {
        return $optionPage->getData($this->getImageWidthColumn());
    }

    /**
     * @param Mana_AttributePage_Model_OptionPage_Store $optionPage
     * @return string|null
     */
    public function getImageHeight($optionPage) {
        return $optionPage->getData($this->getImageHeightColumn());
    }

    /**
     * @return Mana_AttributePage_Resource_OptionPage_Store_Collection|Object
     */
    public function createOptionPageCollection() {
        return Mage::getResourceModel('mana_attributepage/optionPage_store_collection');
    }

    #region Dependencies
    /**
     * @return Mana_Core_Helper_Files|Mage_Core_Helper_Abstract
     */
    public function filesHelper() {
        return Mage::helper('mana_core/files');
    }
    #endregion
}