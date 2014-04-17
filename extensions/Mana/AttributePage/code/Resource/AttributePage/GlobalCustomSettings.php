<?php
/** 
 * @category    Mana
 * @package     Mana_AttributePage
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_AttributePage_Resource_AttributePage_GlobalCustomSettings extends Mana_AttributePage_Resource_AttributePage_Abstract {
    /**
     * Resource initialization
     */
    protected function _construct() {
        $this->_init(Mana_AttributePage_Model_AttributePage_GlobalCustomSettings::ENTITY, 'id');
    }

    /**
     * @param Varien_Object $object
     * @return int
     */
    public function getFinalId($object) {
        $db = $this->getReadConnection();
        $select = $db->select()
            ->from(array('ap_g' => $this->getTable('mana_attributepage/attributePage_global')), 'id')
            ->where("`ap_g`.`attribute_page_global_custom_settings_id` = ?", $object->getId());
        return $db->fetchOne($select);
    }

    public function attributeIdsExists($allAttributeIds) {
        $db = $this->getReadConnection();
        $select = $db->select()
            ->from(array('ap_g' => $this->getTable('mana_attributepage/attributePage_global')), 'id')
            ->where("`ap_g`.`all_attribute_ids` = ?", $allAttributeIds);

        return $db->fetchOne($select);
    }
}