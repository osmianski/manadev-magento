<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterTree
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_FilterTree_Block_Filter extends Mana_Filters_Block_Filter {

    protected function _prepareFilterBlock() {
        $this->_createChildBlocksRecursively($this, $this->getItems());
        return $this;
    }

    /**
     * @param Mage_Core_Block_Template $parent
     * @param $items
     */
    protected function _createChildBlocksRecursively($parent, $items) {
        foreach ($items as $key => /* @var $item Mana_Filters_Model_Item */ $item) {
            $block = $this->getLayout()->createBlock('manapro_filtertree/item', $parent->getNameInLayout().'_'.$key, array(
                'item' => $item,
                'filter' => $this,
                'template' => 'manapro/filtertree/item.phtml',
                'show_in_filter' => $this->getShowInFilter(),
            ));
            $parent->setChild($parent->getNameInLayout() . '_' . $key, $block);
            $this->_createChildBlocksRecursively($block, $item->getItems());
        }
    }
    public function getHtml() {
        $state = Mage::getSingleton('core/session')->getMTreeState();
        Mage::helper('mana_core/js')->options('#m-tree', array_merge(array(
            'url' => $this->getUrl('manapro_filtertree/state/save'),
            'collapsedByDefault' => !Mage::getStoreConfigFlag('mana_filters/tree/expand'),
            'expandSelected' => Mage::getStoreConfigFlag('mana_filters/tree/expand_selected'),
        ), !$state ? array() : array(
            'state' => $state
        )));
        return parent::getHtml();
    }
}