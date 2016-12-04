<?php

class Local_Manadev_Block_Adminhtml_CmsBlockGrid extends Mage_Adminhtml_Block_Cms_Block_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setDefaultSort('update_time');
        $this->setDefaultDir('desc');
    }
}