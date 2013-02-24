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
class ManaPro_Video_Block_Service_Vimeo extends Mage_Core_Block_Template {
    protected function _construct() {
        parent::_construct();
        $this->setTemplate('manapro/video/vimeo.phtml');
    }
    public function getVideoId() {
        return $this->getVideo()->getServiceVideoId();
    }
    public function getWidth() {
        $options = $this->getOptions();
        return $options['width'];
    }
    public function getHeight() {
        $options = $this->getOptions();
        return $options['height'];
    }
    public function getBorder() {
        $options = $this->getOptions();
        return $options['border'];
    }
}