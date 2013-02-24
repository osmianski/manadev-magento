<?php
/**
 * @category    Mana
 * @package     ManaProduct_Tab
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaProduct_Tab_Block_Tabs extends Mage_Core_Block_Template {
    protected $_isLoaded = false;
    protected $_before = array();
    protected $_tabs = array();
    protected $_after = array();

    public function getTabs($display) {
        if (!$this->_isLoaded) {
            $this->load();
        }

        switch ($display) {
            case 'before': return $this->_before;
            case 'tab': return $this->_tabs;
            case 'after': return $this->_after;
            default: return array();
        }
    }

    public function load() {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper(strtolower('Mana_Core'));

        /* @var $block Mage_Catalog_Block_Product_View */
        $block = $this->getParentBlock();
        $tabs = array();
        foreach ($block->getChildGroup('detailed_info') as $childBlock) {
            /* @var $childBlock Mage_Core_Block_Abstract */
            $alias = $childBlock->getBlockAlias();

            /* @var $tab ManaProduct_Tab_Model_Tab */
            $tab = Mage::getModel('manaproduct_tab/tab');
            $tab
                ->setBlock($block)
                ->setAlias($alias)
                ->setTitle($block->getChildData($alias, 'title'))
                ->setDisplay('tab')
                ->setPosition(0);

            $tabs[$alias] = $tab;
        }

        foreach ($core->getSortedXmlChildren(Mage::getConfig()->getNode('manaproduct_tab'), 'tabs') as $key => $options) {
            if ($alias = (string) $options->alias) {
                if (isset($tabs[$alias])) {
                    $tab = $tabs[$alias];
                }
                else {
                    $tab = Mage::getModel('manaproduct_tab/tab');
                    $tab
                        ->setBlock($block)
                        ->setAlias($alias);
                }
                $tab
                    ->setWrapCollateral((bool)((string)$options->wrap_collateral))
                    ->setTitle(Mage::getStoreConfig("manaproduct_tab/$key/title"))
                    ->setDisplay(Mage::getStoreConfig("manaproduct_tab/$key/display"))
                    ->setPosition(Mage::getStoreConfig("manaproduct_tab/$key/position"));

                if (!isset($tabs[$alias])) {
                    $tabs[$alias] = $tab;
                }
            }
        }

        foreach ($tabs as $tab) {
            switch ($tab->getDisplay()) {
                case 'before': $this->_before[] = $tab; break;
                case 'tab': $this->_tabs[] = $tab; break;
                case 'after': $this->_after[] = $tab; break;
            }
        }

        usort($this->_before, array($this, '_compareByPosition'));
        usort($this->_tabs, array($this, '_compareByPosition'));
        usort($this->_after, array($this, '_compareByPosition'));
        $this->_isLoaded = true;
    }

    /**
     * @param ManaProduct_Tab_Model_Tab $a
     * @param ManaProduct_Tab_Model_Tab $b
     * @return int
     */
    public function _compareByPosition($a, $b) {
        if ($a->getPosition() < $b->getPosition()) return -1;
        if ($a->getPosition() > $b->getPosition()) return 1;
        return 0;
    }
}