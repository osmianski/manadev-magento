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
class Mana_Content_Block_Book_ChildPageLinks extends Mage_Core_Block_Template {
    protected $_childPages = array();

    public function _construct() {
        $this->setTemplate('mana/content/book/childPages.phtml');
    }

    /**
     * @return Mana_Content_Model_Page_Abstract
     */
    public function getCurrentBookPage() {
        return Mage::registry('current_book_page');
    }

    protected function loadChildPages($bookPage = null) {
        if (!$bookPage) {
            $bookPage = $this->getCurrentBookPage();
        }
        $bookPage->loadChildPages();
        $childPages = $bookPage->getChildPages();
        $count = count($childPages);
        for($x=0; $x< $count; $x++) {
            if($childPages[$x]->getIsActive() == "0") {
                unset($childPages[$x]);
                continue;
            }
            $route = "mana_content/book/view";
            $id = $childPages[$x]->getId();
            $childPages[$x]->setFinalUrl(Mage::getUrl($route, array('_use_rewrite' => true, 'id' => $id)));
        }

        return $childPages;
    }

    public function getChildPages($bookPage = null) {
        return $this->loadChildPages($bookPage);
    }
}