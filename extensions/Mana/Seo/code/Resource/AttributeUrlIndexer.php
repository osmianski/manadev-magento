<?php
/** 
 * @category    Mana
 * @package     Mana_Seo
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
abstract class Mana_Seo_Resource_AttributeUrlIndexer extends Mana_Seo_Resource_UrlIndexer {
    protected $_matchedEntities = array(
        Mage_Catalog_Model_Resource_Eav_Attribute::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE
        ),
        'mana_filters/filter2' => array(
            Mage_Index_Model_Event::TYPE_SAVE
        ),
        'mana_filters/filter2_store' => array(
            Mage_Index_Model_Event::TYPE_SAVE
        ),
    );

    /**
     * @param Mana_Seo_Model_UrlIndexer $indexer
     * @param Mage_Index_Model_Event $event
     */
    public function register(
        /** @noinspection PhpUnusedParameterInspection */
        $indexer,
        $event
    ) {
        $db = $this->_getReadAdapter();

        if ($event->getEntity() == Mage_Catalog_Model_Resource_Eav_Attribute::ENTITY) {
            $event->addNewData('attribute_id', $event->getData('data_object')->getId());
        }
        elseif ($event->getEntity() == 'mana_filters/filter2') {
            if ($event->getData('data_object')->getType() != 'category') {
                $attributeId = $db->fetchOne($db->select()
                    ->from(array('a' => $this->getTable('eav/attribute')), 'attribute_id')
                    ->joinInner(
                        array('t' => $this->getTable('eav/entity_type')),
                        "`t`.`entity_type_id` = `a`.`entity_type_id` AND `t`.`entity_type_code` = 'catalog_product'",
                        null
                    )
                    ->joinInner(
                        array('ca' => $this->getTable('catalog/eav_attribute')),
                        "`ca`.`attribute_id` = `a`.`attribute_id`",
                        null
                    )
                    ->where('a.attribute_code = ?', $event->getData('data_object')->getCode()));
                $event->addNewData('attribute_id', $attributeId);



            }
        }
        elseif ($event->getEntity() == 'mana_filters/filter2_store') {
            if ($attributeId = $db->fetchOne($db->select()
                ->from(array('a' => $this->getTable('eav/attribute')), 'attribute_id')
                ->joinInner(array('f' => $this->getTable('mana_filters/filter2')), "a.attribute_code = f.code", null)
                ->joinInner(
                    array('t' => $this->getTable('eav/entity_type')),
                    "`t`.`entity_type_id` = `a`.`entity_type_id` AND `t`.`entity_type_code` = 'catalog_product'",
                    null
                )
                ->joinInner(
                    array('ca' => $this->getTable('catalog/eav_attribute')),
                    "`ca`.`attribute_id` = `a`.`attribute_id`",
                    null
                )
                ->where('f.id = ?', $event->getData('data_object')->getGlobalId()))
            ) {
                $event->addNewData('attribute_id', $attributeId);
            }
        }
    }

}