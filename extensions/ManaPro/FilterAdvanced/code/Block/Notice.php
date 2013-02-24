<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterHelp
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_FilterAdvanced_Block_Notice extends Mage_Core_Block_Messages {
    public function _prepareLayout() {
        /* @var $adminHelper Mana_Admin_Helper_Data */
        $adminHelper = Mage::helper(strtolower('Mana_Admin'));
        $store = $adminHelper->getStore();
        if (!Mage::getStoreConfigFlag('mana_filters/advanced/enabled', $store)) {
            $this->addMessage(Mage::getSingleton('core/message')->notice($this->__(
                'Settings from this tab will not be applied until you set %s->%s->%s->%s->%s->%s to %s.',
                $this->__('System'),
                $this->__('Configuration'),
                $this->__('MANAdev'),
                $this->__('Layered Navigation'),
                $this->__('Advanced Display Settings'),
                $this->__('Enable Advanced Display Features'),
                $this->__('Yes')
            )));
        }

        return $this;
    }
}