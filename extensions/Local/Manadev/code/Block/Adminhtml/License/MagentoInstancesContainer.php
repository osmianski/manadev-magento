<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Block_Adminhtml_License_MagentoInstancesContainer extends Mana_Admin_Block_V2_Container {

    public function __construct() {
        parent::__construct();
        $this->_headerText = $this->__('Magento Instances');
    }
}