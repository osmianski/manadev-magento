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
class ManaPro_Video_Model_Service_Vimeo {
    public function toHtml($video, $options) {
        /* @var $layout Mage_Core_Model_Layout */
        $layout = Mage::getSingleton('core/layout');
        $block = $layout->getBlockSingleton('manapro_video/service_vimeo');
        return $block->setVideo($video)->setOptions($options)->toHtml();
    }
}