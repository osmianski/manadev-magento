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
class ManaPro_Guestbook_Block_Posts extends Mage_Core_Block_Template {
    public function getPosts() {
        /* @var $collection ManaPro_Guestbook_Resource_Post_Collection*/
        $collection = Mage::getResourceModel('manapro_guestbook/post_collection');
        $collection->addColumnToSelect('*')->getSelect()
            ->where('status = ?', ManaPro_Guestbook_Model_Post_Status::APPROVED)
            ->order(array('created_at DESC', 'id DESC'));
        return $collection;
    }
    public function getCountry($post) {
        /* @var $country Mage_Directory_Model_Country */ $country = Mage::getModel('directory/country');
        return $country->load($post->getCountryId())->getName();
    }
}