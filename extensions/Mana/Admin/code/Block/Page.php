<?php
/**
 * @category    Mana
 * @package     Mana_Admin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 * @method string getTitle()
 * @method string getTitleGroup()
 * @method string getMenu()
 */
class Mana_Admin_Block_Page extends Mage_Adminhtml_Block_Widget_Container
{
    protected function _construct() {
        parent::_construct();
        $this->setTemplate('mana/admin/page.phtml');
    }

    protected function _prepareLayout() {
        parent::_prepareLayout();

        /* @var $layoutHelper Mana_Core_Helper_Layout */
        $layoutHelper = Mage::helper('mana_core/layout');
        $layoutHelper->delayPrepareLayout($this);

        return $this;

    }

    public function delayedPrepareLayout() {
        $this->_headerText = $this->getTitle();

        return $this;
    }

}