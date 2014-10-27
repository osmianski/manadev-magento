<?php
/**
 * @category    Mana
 * @package     Mana_Content
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Content_TreeController extends Mage_Core_Controller_Front_Action
{
    public function loadAction() {
        $filter = array(
            'current_url' => $this->getRequest()->getPost('current_url'),
            'search'      => $this->getRequest()->getPost('search'),
        );
        Mage::register('filter', $filter);

        $this->loadLayout();
        $this->renderLayout();
    }
}