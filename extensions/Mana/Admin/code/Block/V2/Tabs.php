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
class Mana_Admin_Block_V2_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {
    protected function _construct() {
        $this->setTemplate('mana/admin/v2/tabs.phtml');
    }

    protected function _prepareLayout() {
        parent::_prepareLayout();

        /* @var $layoutHelper Mana_Core_Helper_Layout */
        $layoutHelper = Mage::helper('mana_core/layout');
        $layoutHelper->delayPrepareLayout($this, 1000);

        return $this;

    }

    public function delayedPrepareLayout() {
        $tabs = $this->getChildGroup('tabs');
        if (count($tabs)) {
            $activeTabId = null;
            foreach ($tabs as $tabId => $tab) {
                $tab->setTabId($tabId);
                $this->addTabBlock($tabId, $tab);
                if (!$activeTabId || $tab->getData('active')) {
                    $activeTabId = $tabId;
                }
            }
            $this
                ->setActiveTab($activeTabId)
                ->setDestElementId('tab-content');
        }

        return $this;
    }

    public function addTabBlock($id, $tab) {
        $this->_tabs[$id] = $tab;
        return $this;
    }

    public function getTabId($tab, $withPrefix = true) {
        $withPrefix = false;
        if ($tab instanceof Mage_Adminhtml_Block_Widget_Tab_Interface) {
            $result = ($withPrefix ? $this->getId() . '_' : '') . $tab->getTabId();
        }
        else {
            $result = ($withPrefix ? $this->getId() . '_' : '') . $tab->getId();
        }
        return str_replace('.', '_', $result);
    }

    public function getActiveTabName() {
        if (($tabName = Mage::app()->getRequest()->getParam('tab'))
            && ($tabBlock = $this->getChild($tabName))
            && !$tabBlock->isHidden()
        )
        {
            return $tabName;
        }
        else {
            return key($this->_tabs);
        }
    }

    public function getActiveTabBlock() {
        return $this->getChild($this->getActiveTabName());
    }

    #region Dependencies
    /**
     * @return Mana_Admin_Helper_Data
     */
    public function adminHelper() {
        return Mage::helper('mana_admin');
    }

    /**
     * @return Mana_Db_Helper_Data
     */
    public function dbHelper() {
        return Mage::helper('mana_db');
    }
    #endregion
}