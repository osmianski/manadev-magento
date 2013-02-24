<?php
/** 
 * @category    Mana
 * @package     ManaPro_Guestbook
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_Guestbook_Model_Source_Status extends Mana_Core_Model_Source_Abstract {
    protected function _getAllOptions() {
        return array(
            array('value' => ManaPro_Guestbook_Model_Post_Status::PENDING, 'label' => Mage::helper('manapro_guestbook')->__('Pending')),
            array('value' => ManaPro_Guestbook_Model_Post_Status::APPROVED, 'label' => Mage::helper('manapro_guestbook')->__('Approved')),
            array('value' => ManaPro_Guestbook_Model_Post_Status::REJECTED, 'label' => Mage::helper('manapro_guestbook')->__('Rejected')),
        );
    }
}