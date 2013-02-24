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
class ManaPro_Video_Model_Source_Service_Optional extends ManaPro_Video_Model_Source_Service {
    protected function _getAllOptions() {
        return array_merge(array(array('value' => '', 'label' => '')), parent::_getAllOptions());
    }
}