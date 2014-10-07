<?php
/** 
 * @category    Mana
 * @package     Mana_Content
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Content_Block_ChildPageLinks extends Mage_Core_Block_Template {
    protected $_childPages = array();

    public function _construct() {
        $this->setTemplate('mana/content/book/childPages.phtml');
    }

    protected function _prepareLayout() {
        $this->loadChildPages();
    }

    /**
     * @return Mana_Content_Model_Page_Abstract
     */
    public function getCurrentBookPage() {
        return Mage::registry('current_book_page');
    }

    protected function loadChildPages() {
        $bookPage = $this->getCurrentBookPage();
        $bookPage->loadChildPages();
        $this->_childPages = $bookPage->getChildPages();
        for($x=0; $x<count($this->_childPages); $x++) {
            $route = "content/book/view";
            $id = $this->_childPages[$x]->getId();
            $this->_childPages[$x]->setFinalUrl(Mage::getUrl($route, array('_use_rewrite' => true, 'id' => $id)));
        }
    }

    public function getChildPages() {
        return $this->_childPages;
    }
}