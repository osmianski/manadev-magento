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
class Mana_Menu_TreeController extends Mage_Core_Controller_Front_Action {
    public function saveStateAction() {
        if (($id = $this->getRequest()->getParam('id')) && ($state = $this->getRequest()->getParam('state'))) {
            if ($oldState = Mage::getSingleton('core/session')->getData('m_tree_state_' . $id)) {
                $state = array_merge($oldState, $state);
            }
            Mage::getSingleton('core/session')->setData('m_tree_state_'.$id, $state);
        }
    }
}