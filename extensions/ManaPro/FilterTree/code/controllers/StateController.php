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
class ManaPro_FilterTree_StateController extends Mage_Core_Controller_Front_Action {
    public function saveAction() {
        if ($state = $this->getRequest()->getParam('state')) {
            Mage::getSingleton('core/session')->setMTreeState($state);
        }
    }
}