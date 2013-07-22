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
 * @method string getSourceModel()
 * @method string getSortOrder()
 */
class Mana_Admin_Block_V2_Grid_Action extends Mana_Admin_Block_V2_Action {
    protected static $_massActions = array('remove');
    public function delayedPrepareLayout() {
        parent::delayedPrepareLayout();

        if ($this->_isMassAction()) {
            /* @var $grid Mana_Admin_Block_Grid */
            $grid = $this->getParentBlock();
            $grid->setIsMassActionable(true);
        }
        return $this;
    }

    protected function _isMassAction() {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper(strtolower('Mana_Core'));

        $alias = $this->getBlockAlias();
        if ($alias == $this->getNameInLayout() && $core->startsWith($alias, $this->getParentBlock()->getNameInLayout() . '.')) {
            $alias = substr($alias, strlen($this->getParentBlock()->getNameInLayout() . '.'));
        }

        return in_array($alias, self::$_massActions);
    }
}