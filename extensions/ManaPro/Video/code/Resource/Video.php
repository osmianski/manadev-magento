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
class ManaPro_Video_Resource_Video extends Mana_Db_Resource_Object {
    #region bit indexes for default_mask field(s)
    const DM_SERVICE = 1;
    const DM_SERVICE_VIDEO_ID = 2;
    const DM_POSITION = 3;
    const DM_IS_BASE = 4;
    const DM_IS_EXCLUDED = 5;
    const DM_LABEL = 6;
    const DM_THUMBNAIL = 7;
    #endregion

    /**
     * Invoked during resource model creation process, this method associates this resource model with model class
     * and with DB table name
     */
    protected function _construct() {
        $this->_init('manapro_video/video', 'id');
        $this->_isPkAutoIncrement = false;
    }
    protected function _addEditedData($object, $fields, $useDefault) {
        Mage::helper('mana_db')->updateDefaultableField($object, 'edit_massaction', 0, $fields, $useDefault);
        Mage::helper('mana_db')->updateDefaultableField($object, 'service', ManaPro_Video_Resource_Video::DM_SERVICE, $fields, $useDefault);
        Mage::helper('mana_db')->updateDefaultableField($object, 'service_video_id', ManaPro_Video_Resource_Video::DM_SERVICE_VIDEO_ID, $fields, $useDefault);
        Mage::helper('mana_db')->updateDefaultableField($object, 'position', ManaPro_Video_Resource_Video::DM_POSITION, $fields, $useDefault);
        Mage::helper('mana_db')->updateDefaultableField($object, 'is_base', ManaPro_Video_Resource_Video::DM_IS_BASE, $fields, $useDefault);
        Mage::helper('mana_db')->updateDefaultableField($object, 'is_excluded', ManaPro_Video_Resource_Video::DM_IS_EXCLUDED, $fields, $useDefault);
        Mage::helper('mana_db')->updateDefaultableField($object, 'label', ManaPro_Video_Resource_Video::DM_LABEL, $fields, $useDefault);
        Mage::helper('mana_db')->updateDefaultableField($object, 'thumbnail', ManaPro_Video_Resource_Video::DM_THUMBNAIL, $fields, $useDefault);
    }
}