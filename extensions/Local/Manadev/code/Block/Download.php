<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Block_Download extends Mage_Core_Block_Template
{
    protected $_template = 'local/manadev/download.phtml';

    public function getDownloadUrl() {
        $linkHash = $this->getRequest()->getParam('download');
        return $this->getUrl('downloadable/download/link', array('id' => $linkHash, '_secure' => true));
    }
}