<?php
/**
 * @category    Mana
 * @package     ManaPro_Video
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * Generic helper functions for ManaPro_Video module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class ManaPro_Video_Helper_Data extends Mage_Core_Helper_Abstract {
    public function getBackendVideos($product, $edit) {
        if (Mage::helper('mana_admin')->isGlobal()) {
            $collection = Mage::getResourceModel('manapro_video/video_collection');
        }
        else {
            $collection = Mage::getResourceModel('manapro_video/video_store_collection');
            $collection->addStoreFilter(Mage::helper('mana_admin')->getStore());
            $collection->addColumnToSelect('global_id');
        }
        $collection->addColumnToSelect(array('edit_massaction', 'service', 'service_video_id', 'position',
            'is_base', 'is_excluded', 'label', 'thumbnail'));

        if ($edit) {
            $collection->setEditFilter($edit, "product_id = {$product->getId()}");
        }
        else {
            $collection->setEditFilter(true);
            $collection->addFieldToFilter('product_id', $product->getId());
        }
        return $collection;
    }
    public function getVideos($product) {
        /* @var $collection ManaPro_Video_Resource_Video_Store_Collection */
        $collection = Mage::getResourceModel('manapro_video/video_store_collection');
        $collection->addStoreFilter(Mage::app()->getStore());
        $collection->addColumnToSelect('global_id');
        $collection->addColumnToSelect(array('edit_massaction', 'service', 'service_video_id', 'position',
            'is_base', 'is_excluded', 'label', 'thumbnail'));

        $collection->setEditFilter(true);
        $collection->addFieldToFilter('product_id', $product->getId());
        $collection->setOrder('position', 'ASC');
        return $collection;
    }
    public function getBaseVideo($videos, $all = false) {
        $result = array();
        foreach ($videos as $video) {
            if ($video->getIsBase()) {
                $result[] = $video;
            }
        }
        if (count($result) == 0) {
            return null;
        }
        elseif ($all && count($result) > 1) {
            return $result;
        }
        else {
            return $result[0];
        }
    }
    public function getVisibleVideos($videos) {
        $result = array();
        foreach ($videos as $video) {
            if (!$video->getIsExcluded()) {
                $result[] = $video;
            }
        }
        return $result;
    }
    public function getVideoHtml($video, $options) {
        $model = (string)Mage::getConfig()->getNode('manapro_video/service/' .
                $video->getService() . '/model');
        $model = Mage::getSingleton($model);
        return $model->toHtml($video, $options);
    }
    public function getVideoImage($video, $attributeName, $width, $height = null) {
        if ($video->getThumbnail()) {
            /* @var $image Mage_Catalog_Model_Product_Image */
            $image = Mage::getModel('manapro_video/image');
            $image
                    ->setDestinationSubdir('video-' . $attributeName)
                    ->setBaseFile($video->getThumbnail())
                    ->setWatermark(Mage::getStoreConfig("design/watermark/{$attributeName}_image"))
                    ->setWatermarkImageOpacity(Mage::getStoreConfig("design/watermark/{$attributeName}_imageOpacity"))
                    ->setWatermarkPosition(Mage::getStoreConfig("design/watermark/{$attributeName}_position"))
                    ->setWatermarkSize(Mage::getStoreConfig("design/watermark/{$attributeName}_size"))
                    ->setWidth($width)
                    ->setHeight($height);

            return $image->resize()->saveFile()->getUrl();
        }
        else {
            return Mage::getDesign()->getSkinUrl('images/catalog/product/placeholder/' . $attributeName . '.jpg');
        }
    }
}