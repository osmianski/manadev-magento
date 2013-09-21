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
 * @method bool getBeginEditingSession()
 * @method Mana_Admin_Block_Page setBeginEditingSession(bool $value)
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
        $layoutHelper->delayPrepareLayout($this, 10000);

        return $this;

    }

    public function delayedPrepareLayout() {
        $this->_headerText = $this->getTitle();

        /* @var $pageHelper Mana_Admin_Helper_Page */
        $pageHelper = Mage::helper('mana_admin/page');
        /* @var $left Mage_Adminhtml_Block_Text_List */
        $left = $this->getLayout()->getBlock('left');

        $tabs = $this->getChildGroup('tabs');
        uasort($tabs, array($pageHelper, 'compareBySortOrder'));
        if (count($tabs)) {
            /* @var $tabsBlock Mana_Admin_Block_Tabs */
            $tabsBlock = $this->getLayout()->createBlock('mana_admin/tabs', 'tabs');
            $activeTabId = null;
            foreach ($tabs as $tabId => $tab) {
                $tab->setTabId($tabId);
                $tabsBlock->addTabBlock($tabId, $tab);
                if (!$activeTabId) {
                    $activeTabId = $tabId;
                }
            }
            $tabsBlock
                ->setActiveTab($activeTabId)
                ->setDestElementId('tab-content');
            $left->insert($tabsBlock);
        }

        // create client-side block
        $this->_prepareClientSideBlock();

        return $this;
    }

    public function getButtonsHtml($area = null) {
        /* @var $pageHelper Mana_Admin_Helper_Page */
        $pageHelper = Mage::helper('mana_admin/page');

        return $pageHelper->getActionHtml($this) . parent::getButtonsHtml($area);
    }

    protected function _prepareClientSideBlock() {
        /* @var $urlTemplate Mana_Core_Helper_UrlTemplate */
        $urlTemplate = Mage::helper('mana_core/urlTemplate');

        $this->setMClientSideBlock(array(
            'type' => 'Mana/Admin/Page',
            'url' => $urlTemplate->encodeAttribute($this->getUrl('*/*/{action}')),
        ));

        return $this;
    }
}