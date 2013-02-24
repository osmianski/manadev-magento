<?php
/** 
 * @category    Mana
 * @package     ManaPro_Guestbook
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_Guestbook_Block_List_Container extends Mana_Admin_Block_Crud_List_Container  {
    public function __construct() {
        parent::__construct();
        $this->_headerText = $this->__('Guest Posts');
    }
    protected function _prepareLayout() {
        $this->_addButton('new', array(
            'label' => Mage::helper('catalog')->__('New'),
            'onclick' => "setLocation('{$this->getUrl('*/*/new')}')",
            'class' => 'add'
        ));
    }
}