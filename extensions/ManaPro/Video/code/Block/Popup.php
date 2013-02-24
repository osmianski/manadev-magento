<?php
/**
 * @category    Mana
 * @package     ManaPro_Video
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_Video_Block_Popup extends Mage_Catalog_Block_Product_View_Media {
    public function getVideos() {
        return Mage::helper('manapro_video')->getVideos($this->getProduct());
    }
    public function getVideoHtml($video, $options) {
        return Mage::helper('manapro_video')->getVideoHtml($video, $options);
    }
}