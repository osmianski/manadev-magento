<?php

class Local_Manadev_Block_Adminhtml_CmsPageGrid extends Mage_Adminhtml_Block_Cms_Page_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setDefaultSort('update_time');
        $this->setDefaultDir('desc');
    }
}