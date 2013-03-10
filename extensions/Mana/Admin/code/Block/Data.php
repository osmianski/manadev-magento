<?php
/**
 * @category    Mana
 * @package     Mana_Admin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 * @method string getEntity()
 * @method Mana_Admin_Block_Data_Entity setEntity(string $value)
 */
class Mana_Admin_Block_Data extends Mage_Adminhtml_Block_Template {
    public function getChildDataSources() {
        $result = array();
        $this->_getChildDataSourcesRecursively($result, $this->getParentBlock());
        return $result;
    }

    /**
     * @param array $result
     * @param Mage_Core_Block_Abstract $block
     */
    protected function _getChildDataSourcesRecursively(&$result, $block) {
        foreach ($block->getChild() as $child) {
            if ($child != $this) {
                if ($child instanceof Mana_Admin_Block_Data) {
                    $result[] = $child;
                }
                else {
                    $this->_getChildDataSourcesRecursively($result, $child);
                }
            }
        }
    }
}