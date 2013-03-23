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
class Mana_Admin_Block_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {
    protected function _construct() {
        $this->setTemplate('mana/admin/tabs.phtml');
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

}