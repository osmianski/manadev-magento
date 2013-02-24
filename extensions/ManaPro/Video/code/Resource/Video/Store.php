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
class ManaPro_Video_Resource_Video_Store extends ManaPro_Video_Resource_Video {
    /**
     * Invoked during resource model creation process, this method associates this resource model with model class
     * and with DB table name
     */
    protected function _construct() {
        $this->_init('manapro_video/video_store', 'id');
        $this->_isPkAutoIncrement = false;
    }
    protected function _getReplicationSources() {
        return array('manapro_video/video', 'core/store');
    }
    /**
     * Enter description here ...
     * @param Mana_Db_Model_Replication_Target $target
     */
    protected function _prepareReplicationUpdateSelects($target, $options) {
        $globalEntityName = Mage::helper('mana_db')->getGlobalEntityName($this->getEntityName());
        /* @var $select Varien_Db_Select */
        $select = $options['db']->select();
        $select
                ->from(array('global' => Mage::getSingleton('core/resource')->getTableName($globalEntityName)), null)
                ->joinInner(array('target' => Mage::getSingleton('core/resource')->getTableName($this->getEntityName())),
            'target.global_id = global.id',
            array('target.id AS id', 'target.global_id AS global_id', 'target.store_id AS store_id'))
                ->distinct()
                ->columns(array(
            'target.default_mask0 AS default_mask0',
            'global.product_id AS product_id',
            'global.service AS service',
            'global.service_video_id AS service_video_id',
            'global.position AS position',
            'global.is_base AS is_base',
            'global.is_excluded AS is_excluded',
            'global.label AS label',
            'global.thumbnail AS thumbnail',
        ));
        if ($options['trackKeys']) {
            if (($keys = $options['targets'][$globalEntityName]->getSavedKeys()) && count($keys)) {
                $select->where('global.id IN (?)', $keys);
                $target->setIsKeyFilterApplied(true);
            }
            if (($keys = $options['targets'][$this->getEntityName()]->getSavedKeys()) && count($keys)) {
                $select->where('target.id IN (?)', $keys);
                $target->setIsKeyFilterApplied(true);
            }
        }
        $target->setSelect('main', $select);
    }
    /**
     * Enter description here ...
     * @param Mana_Db_Model_Object $object
     * @param array $values
     * @param array $options
     */
    protected function _processReplicationUpdate($object, $values, $options) {
        $object
                ->setId($values['id'])
                ->setGlobalId($values['global_id'])
                ->setStoreId($values['store_id'])
                ->setData('_m_prevent_replication', true);

        $object->setProductId($values['product_id']);
        if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, ManaPro_Video_Resource_Video::DM_SERVICE)) {
            $object->setService($values['service']);
        }
        if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, ManaPro_Video_Resource_Video::DM_SERVICE_VIDEO_ID)) {
            $object->setServiceVideoId($values['service_video_id']);
        }
        if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, ManaPro_Video_Resource_Video::DM_POSITION)) {
            $object->setPosition($values['position']);
        }
        if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, ManaPro_Video_Resource_Video::DM_IS_BASE)) {
            $object->setIsBase($values['is_base']);
        }
        if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, ManaPro_Video_Resource_Video::DM_IS_EXCLUDED)) {
            $object->setIsExcluded($values['is_excluded']);
        }
        if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, ManaPro_Video_Resource_Video::DM_LABEL)) {
            $object->setLabel($values['label']);
        }
        if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, ManaPro_Video_Resource_Video::DM_THUMBNAIL)) {
            $object->setThumbnail($values['thumbnail']);
        }

    }
    /**
     * Enter description here ...
     * @param Mana_Db_Model_Replication_Target $target
     */
    protected function _prepareReplicationInsertSelects($target, $options) {
        $globalEntityName = Mage::helper('mana_db')->getGlobalEntityName($this->getEntityName());
        /* @var $select Varien_Db_Select */
        $select = $options['db']->select();
        $select
                ->from(array('global' => Mage::getSingleton('core/resource')->getTableName($globalEntityName)), 'global.id AS global_id')
                ->from(array('core_store' => Mage::getSingleton('core/resource')->getTableName('core_store')), 'core_store.store_id AS store_id')
                ->joinLeft(array('target' => Mage::getSingleton('core/resource')->getTableName($this->getEntityName())),
            'target.global_id = global.id AND target.store_id = core_store.store_id', null)
                ->distinct()
                ->where('core_store.store_id <> 0')
                ->where('target.id IS NULL')
                ->columns(array(
            'global.product_id AS product_id',
            'global.service AS service',
            'global.service_video_id AS service_video_id',
            'global.position AS position',
            'global.is_base AS is_base',
            'global.is_excluded AS is_excluded',
            'global.label AS label',
            'global.thumbnail AS thumbnail',
        ));
        if ($options['trackKeys']) {
            if (($keys = $options['targets'][$globalEntityName]->getSavedKeys()) && count($keys)) {
                $select->where('global.id IN (?)', $keys);
                $target->setIsKeyFilterApplied(true);
            }
        }
        $target->setSelect('main', $select);
    }
    /**
     * Enter description here ...
     * @param Mana_Db_Model_Object $object
     * @param array $values
     * @param array $options
     */
    protected function _processReplicationInsert($object, $values, $options) {
        $object
                ->setGlobalId($values['global_id'])
                ->setStoreId($values['store_id'])
                ->setData('_m_prevent_replication', true);

        $object->setProductId($values['product_id']);
        $object->setService($values['service']);
        $object->setServiceVideoId($values['service_video_id']);
        $object->setPosition($values['position']);
        $object->setIsBase($values['is_base']);
        $object->setIsExcluded($values['is_excluded']);
        $object->setLabel($values['label']);
        $object->setThumbnail($values['thumbnail']);
    }
    /**
     * Enter description here ...
     * @param Mana_Db_Model_Replication_Target $target
     */
    protected function _prepareReplicationDeleteSelects($target, $options) {
    }
    /**
     * Enter description here ...
     * @param array $values
     * @param array $options
     */
    protected function _processReplicationDelete($values, $options) {
    }
}