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
class Mana_Content_Block_Adminhtml_Book_Tabs extends Mana_Admin_Block_V2_Tabs {
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('widget/tabshoriz.phtml');
    }
}