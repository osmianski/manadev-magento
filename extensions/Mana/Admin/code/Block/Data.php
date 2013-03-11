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
    /**
     * @return Mana_Admin_Block_Data_Entity
     */
    public function getParentDataSource() {
        /* @var $admin Mana_Admin_Helper_Data */
        $admin = Mage::helper('mana_admin');

        for ($block = $this->getParentBlock()->getParentBlock(); $block != null; $block = $block->getParentBlock()) {
            if ($dataSource = $admin->getDataSource($block)) {
                return $dataSource;
            }
        }
        return null;
    }

    /**
     * @return Mana_Admin_Block_Data[]
     */
    public function getChildDataSources() {
        $result = array();
        $this->_getChildDataSourcesRecursively($result, $this->getParentBlock());
        return $result;
    }

    /**
     * @param Mana_Admin_Block_Data[] $result
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