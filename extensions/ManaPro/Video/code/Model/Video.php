<?php
/**
 * @category    Mana
 * @package     ManaPro_Video
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_Video_Model_Video extends Mana_Db_Model_Object {
    /**
     * Invoked during model creation process, this method associates this model with resource and resource
     * collection classes
     */
    protected function _construct() {
        $this->_init('manapro_video/video');
    }
    protected function _validate($result) {
        $t = Mage::helper('manapro_video');
        if (trim($this->getService()) === '') {
            $result->addError($t->__('Please fill in %s column', $t->__('Video Service')));
        }
        if (trim($this->getServiceVideoId()) === '') {
            $result->addError($t->__('Please fill in %s column', $t->__('Video ID')));
        }
        if (trim($this->getPosition()) === '') {
            $result->addError($t->__('Please fill in %s column', $t->__('Position')));
        }

    }
    public function afterCommitCallback() {
        $object = $this;
        $files = Mage::helper(strtolower('Mana_Core/Files'));
        foreach (array('thumbnail') as $field) {
            if ($relativeUrl = $object->getData($field)) {
                if ($sourcePath = $files->getFilename($relativeUrl, 'temp/image')) {
                    $targetPath = $files->getFilename($relativeUrl, 'image', true);
                    if (file_exists($targetPath)) {
                        unlink($targetPath);
                    }
                    copy($sourcePath, $targetPath);
                    unlink($sourcePath);
                }

            }
        }
        return parent::afterCommitCallback();
    }
    protected function _assignDefaultValues() {
        $this->setService(Mage::getStoreConfig('manapro_video/service/default'));
    }

}