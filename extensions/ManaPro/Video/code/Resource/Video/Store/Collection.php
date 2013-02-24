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
class ManaPro_Video_Resource_Video_Store_Collection extends ManaPro_Video_Resource_Video_Collection {
    /**
     * Invoked during resource collection model creation process, this method associates this
     * resource collection model with model class and with resource model class
     */
    protected function _construct() {
        $this->_init('manapro_video/video_store');
    }
}