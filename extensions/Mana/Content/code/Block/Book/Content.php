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
class Mana_Content_Block_Book_Content extends Mage_Core_Block_Template {

    public function __construct() {
        $this->setTemplate('mana/content/book/content.phtml');
    }

    public function parseContent() {
        if($this->getCurrentBookPage()) {
            $content = $this->getCurrentBookPage()
                ->getData('content');
            if(Mage::helper('core')->isModuleEnabled('SchumacherFM_Markdown')) {
                return Mage::helper("markdown")->render($content);
            } else {
                return $content;
            }
        }
        return;
    }

    /**
     * @return Mana_Content_Model_Page_Abstract
     */
    public function getCurrentBookPage() {
        return Mage::registry('current_book_page');
    }

    public function getTitle() {
        return $this->getCurrentBookPage()->getTitle();
    }

}