<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Block_Download extends Mage_Core_Block_Template
{
    protected $_template = 'local/manadev/download.phtml';

    public function getDownloadUrl() {
        return $this->getUrl('downloadable/download/link', array('id' => $this->getLinkHash(), '_secure' => true));
    }

    protected function getLinkHash() {
        return $this->getData('link_hash');
    }
}