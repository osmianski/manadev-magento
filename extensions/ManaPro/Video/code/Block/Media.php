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
class ManaPro_Video_Block_Media extends Mage_Catalog_Block_Product_View_Media {
    protected function _construct() {
        parent::_construct();
        $this->setTemplate('manapro/video/media.phtml');
    }

    public function getVideos() {
        return Mage::helper('manapro_video')->getVideos($this->getProduct());
    }
    public function getVideoHtml($video, $options) {
        return Mage::helper('manapro_video')->getVideoHtml($video, $options);
    }
    public function getMedias($videos, $images) {
        $result = array();
        foreach ($videos as $data) {
            $result[] = array('type' => 'video', 'data' => $data, 'position' => $data->getPosition());
        }
        foreach ($images as $data) {
            $result[] = array('type' => 'image', 'data' => $data, 'position' => $data->getPosition());
        }
        usort($result, array($this, '_compareMedias'));
        return $result;
    }
    public function _compareMedias($a, $b) {
        if ($a['position'] < $b['position']) return -1;
        if ($a['position'] > $b['position']) return 1;
        return 0;
    }
    protected function _beforeToHtml() {
        /* @var $js Mana_Core_Helper_Js */ $js = Mage::helper(strtolower('Mana_Core/Js'));
        $js->options('#m-video', array(
            'url' => $this->getUrl('m-video/product/popup', array(
                'id' => $this->getProduct()->getId(),
                'start' => '__',
            )),
            'debug' => Mage::getStoreConfigFlag('manapro_video/ajax/debug'),
            'progress' => Mage::getStoreConfigFlag('manapro_video/ajax/progress'),
            'fadeOut' => array(),
            'fadeIn' => array(),
        ));
        return parent::_beforeToHtml();
    }
}