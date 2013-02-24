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
class ManaPro_Video_Model_Video_Store extends ManaPro_Video_Model_Video {
    /**
     * Invoked during model creation process, this method associates this model with resource and resource
     * collection classes
     */
    protected function _construct() {
        $this->_init('manapro_video/video_store');
    }
}
