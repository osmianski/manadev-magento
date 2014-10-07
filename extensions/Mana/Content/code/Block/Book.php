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
class Mana_Content_Block_Book extends Mage_Core_Block_Template {

    public function __construct() {
        $this->setTemplate('mana/content/book/book.phtml');
    }

    public function parseContent() {
        if($this->getCurrentBookPage()) {
            $content = $this->getCurrentBookPage()
                ->getData('content');
            return Mage::helper("markdown")->render($content);
        }
        return;
    }

    /**
     * @return Mana_Content_Model_Page_Abstract
     */
    public function getCurrentBookPage() {
        return Mage::registry('current_book_page');
    }

}