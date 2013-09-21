<?php
/**
 * @category    Mana
 * @package     Mana_Admin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Admin_Block_Tab extends Mage_Adminhtml_Block_Text_List implements Mage_Adminhtml_Block_Widget_Tab_Interface {
    #region 3-phase construction
    protected function _prepareLayout() {
        /* @var $layoutHelper Mana_Core_Helper_Layout */
        $layoutHelper = Mage::helper('mana_core/layout');
        $layoutHelper->delayPrepareLayout($this);

        return $this;
    }

    public function delayedPrepareLayout() {
        $this->addToParentGroup('tabs');

        /* @var $pageBlock Mana_Admin_Block_Page */
        if ($pageBlock = $this->getLayout()->getBlock('page')) {
            $pageBlock->setBeginEditingSession(true);
        }
        // create client-side block
        $this->_prepareClientSideBlock();

        return $this;
    }
    #endregion

    #region Client side block support
    protected function _prepareClientSideBlock() {
        $this->setMClientSideBlock(array(
            'type' => 'Mana/Admin/Tab',
        ));

        return $this;
    }
    #endregion

    #region Mage_Adminhtml_Block_Widget_Tab_Interface
    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel() {
        return $this->getTitle();
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle() {
        return $this->getTitle();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab() {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden() {
        return false;
    }

    #endregion
}