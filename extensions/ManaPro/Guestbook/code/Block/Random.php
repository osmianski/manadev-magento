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
class ManaPro_Guestbook_Block_Random extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface {
    protected $_postInitialized = false;
    protected $_posts;

    /**
     * @return ManaPro_Guestbook_Resource_Post_Collection
     */
    public function getAllPosts() {
        /* @var $collection ManaPro_Guestbook_Resource_Post_Collection*/
        $collection = Mage::getResourceModel('manapro_guestbook/post_collection');
        $collection->addColumnToSelect('*')->getSelect()
            ->where('status = ?', ManaPro_Guestbook_Model_Post_Status::APPROVED)
            ->order(array('created_at DESC', 'id DESC'));
        return $collection;
    }

    public function getPosts() {
        if (!$this->_postInitialized) {
            $collection = $this->getAllPosts();
            $this->_posts = array();
            if ($ids = $collection->getAllIds()) {
                $expectedPostCount = $this->getPostCount() ? $this->getPostCount() : 1;
                $maxPostCount = count($ids);
                if ($maxPostCount > $expectedPostCount) {
                    $maxPostCount = $expectedPostCount;
                }
                for ($i = 0; $i < $maxPostCount;) {
                    $id = $ids[rand(0, count($ids) - 1)];
                    if (!isset($this->_posts[$id])) {
                        $this->_posts[$id] = Mage::getModel('manapro_guestbook/post')->load($id);
                        $i++;
                    }
                }
            }
            $this->_postInitialized = true;
        }
        return $this->_posts;
    }

    public function getReadMoreUrl($post)
    {
        return $this->getUrl('guest/book');
    }
}