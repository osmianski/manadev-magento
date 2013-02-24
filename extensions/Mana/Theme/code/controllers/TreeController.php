<?php
/**
 * @category    Mana
 * @package     Mana_Theme
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Theme_TreeController extends Mage_Core_Controller_Front_Action {
    public function stateAction() {
        if (($selector = $this->getRequest()->getParam('selector')) && ($state = $this->getRequest()->getParam('state'))) {
            $states = Mage::getSingleton('core/session')->getMTreeStates();
            if (!$states) {
                $states = array();
            }
            $states[$selector] = $state;
            Mage::getSingleton('core/session')->setMTreeStates($states);
        }
    }
}