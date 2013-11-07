<?php
/** 
 * @category    Mana
 * @package     Mana_Menu
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Menu_Block_Tree extends Mana_Menu_Block_Abstract implements Mage_Widget_Block_Interface {
    public function delayedPrepareLayout() {
        // prepare client side settings
        $clientSideBlock = array(
            'element' => 'ul',
            'class' => 'm-menu-tree',
            'type' => 'Mana/Menu/Tree',
            'expand-by-default' => $this->getExpandByDefault(),
            'collapse-selected' => $this->getCollapseSelected(),
            'url' => Mage::helper('mana_core/urlTemplate')->encodeAttribute($this->getStateUrl()),
        );
        if ($state = $this->getState()) {
            $clientSideBlock['state'] = Mage::helper('mana_core/json')->encodeAttribute($state);
        }
        $this->setData('m_client_side_block', $clientSideBlock);

        $this->setTemplate('mana/menu/tree.phtml');

        $this->_createItemBlocksRecursively($this->_getItems());
    }

    protected function _getItems() {
        if ($xml = $this->getXml()) {
            $this->_prepareXmlItems($xml, array($this, '_selectItemIfRouteIsCurrent'));
            return $this->_getXmlItems($xml);
        }
        else {
            return array();
        }
    }

    public function getSkipEscapingLabels() {
        return $this->_getData('skip_escaping_labels') || ($xml = $this->getXml()) && !empty($xml->skip_escaping_labels);
    }

    public function getExpandByDefault() {
        return $this->_getData('expand_by_default') || ($xml = $this->getXml()) && !empty($xml->expand_by_default);
    }

    public function getCollapseSelected() {
        return $this->_getData('collapse_selected') || ($xml = $this->getXml()) && !empty($xml->collapse_selected);
    }

    public function getStateUrl() {
        return Mage::getUrl('mana/tree/saveState', array(
            '_secure' => Mage::app()->getFrontController()->getRequest()->isSecure()
        ));
    }

    public function getState() {
        $id = Mage::helper('mana_core/js')->getClientSideBlockName($this->getNameInLayout());
        return Mage::getSingleton('core/session')->getData('m_tree_state_' . $id);
    }

    /**
     * @param SimpleXMLElement $xml
     * @param string | bool $parentId
     * @return array
     */
    protected function _getXmlItems($xml, $parentId = false) {
        $items = array();
        if (!empty($xml->items)) {
            foreach ($xml->items->children() as $id => $itemXml) {
                $items[] = $this->_createItem($this->_getXmlItem($parentId ? $parentId.'.'.$id : $id, $itemXml));
            }
        }
        return $items;
    }
    /**
     * @param string $itemId
     * @param SimpleXMLElement $xml
     * @return array
     */
    protected function _getXmlItem($itemId, $xml) {
        /** @noinspection PhpUndefinedFieldInspection */
        $result = array(
            'id' => $itemId,
            'label' => (string)$xml->label,
            'items' => $this->_getXmlItems($xml, $itemId),
        );
        if (!empty($xml->hidden)) {
            $result['hidden'] = true;
        }
        if (!empty($xml->selected)) {
            $result['selected'] = true;
        }
        else {
            if (!empty($xml->url)) {
                $result['url'] = (string)$xml->url;
            }
            elseif (!empty($xml->direct_url)) {
                $result['url'] = Mage::getUrl(null, array('_direct' => (string)$xml->direct_url));
            }
            elseif (!empty($xml->route)) {
                $result['url'] = Mage::getUrl((string)$xml->route);
            }
        }

        return $result;
    }

    protected function _createItem($item) {
        $item['tree'] = $this;
        if (!$this->getSkipEscapingLabels()) {
            $item['label'] = $this->escapeHtml($item['label']);
        }
        return $item;
    }


    /**
     * @param SimpleXMLElement $xml
     * @param callable $callback
     * @param bool| Varien_Object $context
     * @return Varien_Object
     */
    protected function _prepareXmlItems($xml, $callback, $context = false) {
        if (!$context) {
            $context = new Varien_Object();
        }
        if (!empty($xml->items)) {
            foreach ($xml->items->children() as $itemXml) {
                $this->_prepareXmlItem($itemXml, $callback, $context);
            }
        }
        return $context;
    }

    /**
     * @param SimpleXMLElement $xml
     * @param callable $callback
     * @param Varien_Object $context
     * @return array
     */
    protected function _prepareXmlItem($xml, $callback, $context) {
        call_user_func($callback, $xml, $context);
        $this->_prepareXmlItems($xml, $callback, $context);
    }

    /**
     * @param array $items
     * @param Mage_Core_Block_Abstract | bool $parentBlock
     */
    protected function _createItemBlocksRecursively($items, $parentBlock = false) {
        if (!$parentBlock) {
            $parentBlock = $this;
        }
        foreach ($items as $item) {
            $childItems = $item['items'];
            unset($item['items']);
            $id = $item['id'];
            $childBlock = $this->getLayout()->createBlock('mana_menu/tree_item', $this->getNameInLayout().'_'. $id, $item);
            $parentBlock->setChild($id, $childBlock);
            $this->_createItemBlocksRecursively($childItems, $childBlock);
        }
    }

    protected function _selectItemIfRouteIsCurrent($xml) {
        if (!empty($xml->route)) {
            $core = Mage::helper('mana_core');
            if ($core->getRoutePath().$core->getRouteParams() == (string)$xml->route) {
                $xml->selected = 1;
            }
        }
    }
}