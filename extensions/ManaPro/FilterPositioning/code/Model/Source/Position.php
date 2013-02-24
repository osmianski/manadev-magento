<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterPositioning
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_FilterPositioning_Model_Source_Position {
    protected $_position = '';
    protected function _getAllOptions() {
        $result = array();
        foreach (Mage::getConfig()->getNode('mana_filters/positioning/' . $this->_position)->children() as $node) {
            $result[] = array(
                'value' => (string)$node->template,
                'label' => Mage::helper('manapro_filterpositioning')->__((string)$node->label)
            );
        }
        return $result;
    }
    public function toOptionArray() {
        return $this->_getAllOptions();
    }
    public function getCurrentNode($position) {
        $template = Mage::getStoreConfig('mana_filters/positioning/' . $position);
        if ($positionNode = Mage::getConfig()->getNode('mana_filters/positioning/' . $position)) {
            foreach ($positionNode->children() as $node) {
                if ((string)$node->template == $template) {
                    return $node;
                }
            }
        }
        return null;
    }
}